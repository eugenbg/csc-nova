<?php

namespace App\Console\Commands;

use App\Models\DonorPage;
use App\Models\Spell;
use App\Services\TextGenerationService;
use App\Services\UniquenessTestingService;
use DOMElement;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;

class PrepareFTData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prepare-ft {--rewrite} {--export} {--ft} {--listFiles} {--upload} {--ft-file=} {--listFineTunes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private $level = 0;
    /**
     * @var UniquenessTestingService
     */
    protected $uService;
    /**
     * @var Spell|Spell[]|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    private $spell;
    private $paragraphs = [];
    /**
     * @var DOMElement|null
     */
    private $currentParent;
    private $i;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UniquenessTestingService $uService)
    {
        parent::__construct();
        $this->uService = $uService;
        $this->spell = Spell::query()->find(16);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if($this->option('rewrite')) {
            $this->rewriteFTPieces();
        } elseif($this->option('export')) {
            $this->export();
        } elseif($this->option('ft')) {
            $this->fineTune();
        } elseif($this->option('upload')) {
            $this->upload();
        } elseif($this->option('listFiles')) {
            $this->listFiles();
        }  elseif($this->option('listFineTunes')) {
            $this->listFineTunes();
        } else {
            $this->parse();
        }
    }


    private function parsePageContent(DonorPage $page)
    {
        $response = $this->client->get($page->url);
        $crawler = new Crawler($response->getBody()->__toString());

        $this->i = 0;
        if(!$crawler->filter('.entry-content.entry')->count()) {
            return;
        }

        $crawler->filter('.entry-content.entry')
            ->first()
            ->children('*')
            ->each(function (Crawler $node) {
                $this->loop($node->getNode(0));
            });
    }

    /**
     * @param DOMElement|\DOMText $element
     * @return void
     */
    private function loop($element)
    {
        if(get_class($element) !== DOMElement::class) {
            return;
        }

        if(in_array($element->tagName, ['p', 'h1', 'h2', 'ul', 'ol'])) {
            if($element->parentNode === $this->currentParent) {

                if($element->tagName == 'ul') {
                    $crawler = new Crawler($element);
                    $content = [];
                    foreach ($crawler->children('li') as $item) {
                        $content[] = $item->textContent;
                    }
                } else {
                    $content = $element->textContent;
                }

                $this->paragraphs[$this->i][] = [
                    'content' => $content,
                    'type' => $element->tagName
                ];
            } else {
                $this->i++;
                $this->currentParent = $element->parentNode;
            }
        } elseif(count($element->childNodes)) {
            foreach ($element->childNodes as $childNode) {
                $this->loop($childNode);
            }
        }
    }

    private function parse()
    {
        //DB::table('ft_pieces')->truncate();
        $this->client = new \GuzzleHttp\Client();
        $pages = DonorPage::query()
            ->where('run', '=', 2)
            ->get();

        /** @var DonorPage $page */
        foreach ($pages as $key => $page) {
            $this->paragraphs = [];

            $this->parsePageContent($page);
            $pieces = [];
            foreach ($this->paragraphs as $groups) {
                if(count($groups) < 5) {
                    $text = '';
                    foreach ($groups as $contentPiece) {
                        if($contentPiece['type'] == 'p') {
                            $text .= "\n" . $contentPiece['content'];
                        }
                    }

                    $pieces[] = trim($text);
                } else {
                    foreach ($groups as $contentPiece) {
                        if($contentPiece['type'] == 'p') {
                            $pieces[] = trim($contentPiece['content']);
                        }
                    }
                }
            }

            $payload = [];
            foreach ($pieces as $piece) {
                $payload[] = [
                    'content' => $piece
                ];
            }

            DB::table('ft_pieces')->insert($payload);

            $this->info('finished page ' . $key);
            $this->info('added #' . count($payload) . ' pieces');
        }
    }

    private function rewriteFTPieces()
    {
        $rows = DB::table('ft_pieces')
            ->whereNull('rewritten')
            ->whereRaw('length(content) > 150')
            ->whereRaw('length(content) < 400')
            ->limit(250)
            ->get();

        foreach ($rows as $key => $row) {
            $this->level = 0;
            $rewritten = $this->rewrite($row->content);

            $resembling = (int) $this->uService->run($row->content, $rewritten);
            if($rewritten) {
                DB::table('ft_pieces')
                    ->where('id', '=', $row->id)
                    ->update([
                        'rewritten' => $rewritten,
                        'unique' => $resembling,
                    ]);
            } else {
                $this->error('could not rewrite piece');
                DB::table('ft_pieces')
                    ->where('id', '=', $row->id)
                    ->update([
                        'failed' => true,
                    ]);
                continue;
            }

            $this->info('finished piece #' . $key);
        }
    }

    private function rewrite($text)
    {
        if ($this->level > 1) {
            return null;
        }

        $this->level++;

        $rewrites = TextGenerationService::generate($text, $this->spell, 5);

        $filtered = [];
        foreach ($rewrites as $rewrite) {

            $rewrite = trim($rewrite);
            if(!strlen($rewrite)) {
                continue;
            }

            $resembling = $this->uService->run($text, $rewrite);
            if ($resembling < 15) {
                $filtered[$resembling] = $rewrite;
            }
        }

        if (count($filtered) > 0) {
            if (count($filtered) > 1) {
                ksort($filtered);
            }
            return array_shift($filtered);
        } else {
            $this->info('going for an extra round');
            return $this->rewrite($text);
        }
    }

    private function export()
    {
        $rows = DB::table('ft_pieces')
            ->where('chosen', '=', 1)
            ->get();

        $payload = [];
        foreach ($rows as $row) {
            $payload[] = [
                'prompt' => $row->content,
                'completion' => $row->rewritten
            ];
        }


/*        $path = storage_path('test.csv');
        Helper::arrayToCsv($path, $payload);
*/

        $path = storage_path('test.jsonl');
        $file = fopen($path,"a");
        foreach ($payload as $item) {
            fwrite($file,json_encode($item) . PHP_EOL);
        }

        fclose($file);
    }

    private function upload()
    {
        $client = new Client();
        $response = $client->request('POST', 'https://api.openai.com/v1/files', [
            'headers' => ['Authorization' => sprintf('Bearer %s', env('OPENAI_API_KEY'))],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => file_get_contents(storage_path('test_prepared.jsonl')),
                    'filename' => 'test_prepared2.jsonl'
                ],
                [
                    'name'     => 'purpose',
                    'contents' => 'fine-tune',
                ],
            ],
        ]);

        $result = json_decode($response->getBody()->__toString(), true);
        $this->info('uploaded file id');
        $this->info($result["id"]);
    }

    public function listFiles()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.openai.com/v1/files', [
            'headers' => ['Authorization' => sprintf('Bearer %s', env('OPENAI_API_KEY'))],
        ]);
        $result = json_decode($response->getBody()->__toString(), true);
        foreach ($result["data"] as $datum) {
            $this->info($datum['id'] . ' filename: ' . $datum['filename']);
        }
    }

    private function fineTune()
    {
        $fileId = $this->option('ft-file');
        if(!$fileId) {
            $this->error('need file id, option --ft-file');
            return;
        }

        $client = new Client();
        $response = $client->request('POST', 'https://api.openai.com/v1/fine-tunes', [
            'headers' => ['Authorization' => sprintf('Bearer %s', env('OPENAI_API_KEY'))],
            'json' => [
                'training_file' => $fileId,
                'model' => 'curie'
            ],
        ]);

        $result = json_decode($response->getBody()->__toString(), true);
        $this->info('new fine tuned model id');
        $this->info($result["id"]);
    }

    private function listFineTunes()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.openai.com/v1/fine-tunes', [
            'headers' => ['Authorization' => sprintf('Bearer %s', env('OPENAI_API_KEY'))],
        ]);

        $result = json_decode($response->getBody()->__toString(), true);
        foreach ($result["data"] as $key => $datum) {
            $this->info($key);
            $this->info(sprintf('model: %s, id: %s, status: %s', $datum['model'], $datum['id'], $datum['status']));
            $this->info(sprintf('model name: %s', $datum['fine_tuned_model']));
        }
    }

}
