<?php

namespace App\Console\Commands;

use App\Helper;
use App\Http\Resources\ContentPiece;
use App\Models\DonorPage;
use DOMElement;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\DomCrawler\Crawler;

class ParseDonorPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse-donor {--truncate} {--csv=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var Client
     */
    private $client;
    private $paragraphs = [];
    private $currentParent = null;
    private $i = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('truncate')) {
            DonorPage::query()->truncate();
        }

        if ($csv = $this->option('csv')) {
            $this->saveFromCsv($csv);
        }

        $this->parseContent();

        return 0;
    }

    private function saveFromCsv($csvFile)
    {
        $path = storage_path($csvFile);
        $data = collect(Helper::csvToArray($path));
        $grouped = $data->groupBy('URL');

        $payload = [];
        foreach ($grouped as $url => $entries) {

            $qty = DonorPage::query()
                ->where('url', '=', $url)
                ->count();
            if ($qty) {
                continue;
            }

            $keywords = Arr::pluck($entries, 'Keyword');
            $payload[] = [
                'donor_id' => 1,
                'url' => $url,
                'keywords' => json_encode($keywords),
                'keywords_qty' => count($keywords),
            ];
        }

        DonorPage::query()->insert($payload);
    }

    private function parseContent()
    {
        $this->client = new Client();
        $pages = DonorPage::query()
            //->whereNull('content_pieces')
            //->where('id', '>', 81)
            ->get();

        /** @var DonorPage $page */
        foreach ($pages as $page) {
            $this->parsePageContent($page);
            $page->save();
        }
    }

    private function parsePageContent(DonorPage $page)
    {
        $this->paragraphs = [];

        $response = $this->client->get($page->url);
        $crawler = new Crawler($response->getBody()->__toString());

        $page->content = $response->getBody()->__toString();

        if($crawler->filter('.entry-content.entry ul li ul')->count()) {
            return;
        }

        if (!$crawler->filter('.entry-content.entry')->count()) {
            return;
        }

        if(!$crawler->filter('.post-cat-wrap a')->count()) {
            return;
        }

        $categories = [];
        $categoryElements = $crawler->filter('.post-cat-wrap a');
        foreach ($categoryElements as $categoryElement) {
            $categories[] = $this->cleanString($categoryElement->textContent);
        }

        $page->categories = $categories;

        $crawler->filter('.entry-content.entry')
            ->first()
            ->children('*')
            ->each(function (Crawler $node) {
                $this->loop($node->getNode(0));
            });

        $page->content_pieces = $this->paragraphs;
        $page->save();
    }

    /**
     * @param DOMElement|\DOMText $element
     * @return void
     */
    private function loop($element)
    {
        if (get_class($element) !== DOMElement::class) {
            return;
        }

        if (in_array($element->tagName, ['p', 'b', 'em', 'h1', 'h2', 'ul', 'ol'])) {
            if ($element->parentNode !== $this->currentParent) {
                $this->i++;
                $this->currentParent = $element->parentNode;
            }

            $this->saveElementContent($element);
        } elseif (count($element->childNodes)) {
            foreach ($element->childNodes as $childNode) {
                $this->loop($childNode);
            }
        }
    }

    public function cleanString($string)
    {
        return rtrim(trim($string), ':-');
    }

    private function saveElementContent(DOMElement $element)
    {
        $type = $element->tagName;
        if ($element->tagName == 'ul') {
            $crawler = new Crawler($element);
            if ($crawler->filter('li')->count() == 1) {
                $type = 'h2';//if it's a list with one element then it must be a heading
                $content = $crawler->filter('li')->first()->text();
                $content = trim($content);
            } else {
                $content = [];
                foreach ($crawler->children('li') as $item) {
                    $content[] = $item->textContent;
                }
            }
        } else {
            $content = $element->textContent;
        }

        if (is_string($content) && strlen($content)) {
            $this->paragraphs[$this->i][] = [
                'content' => $this->cleanString($content),
                'type' => $type
            ];
        }

        if (is_array($content) && count($content)) {
            $this->paragraphs[$this->i][] = [
                'content' => $content,
                'type' => $type
            ];
        }
    }
}
