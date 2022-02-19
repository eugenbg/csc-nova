<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\ChinaUniversity;
use App\Models\ContentChunk;
use App\Models\Spell;
use App\Services\TextGenerationService;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class GenerateContentChunk extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chunk:rewrite {piece}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $sourceArticle = 'china-uni-flat'; //todo make it a param
        $piece = $this->argument('piece');
        $qty = 20;

        $chunk = ContentChunk::query()
            ->where('source_article', '=', $sourceArticle)
            ->where('type', '=', 'original')
            ->where('piece', '=', $piece)
            ->first();

        if(!$chunk) {
            return;
        }

        $paragraph = str_replace('%uni', 'Central South University', $chunk->text);
        $stopWords = ['Central South University', 'CSU', 'CSCU', 'SFU'];

        $spell = Spell::find(5);

        $paragraph = str_replace(['<h2>', '</h2>'], '', $paragraph);
        $paragraph = str_replace(['</li>'], "\n", $paragraph);
        $paragraph = str_replace(['<li>'], '-', $paragraph);
        $paragraph = strip_tags($paragraph);
        $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
        $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
        $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
        $paragraph = str_replace(["-\r\n", "-\n"], "-", $paragraph);
        $paragraph = ltrim(rtrim($paragraph));
        $texts = TextGenerationService::generate($paragraph, $spell, $qty);
        $existingTexts = $this->getExistingTexts($sourceArticle, $piece);
        $nonDuplicateTexts = [];
        /** @var UniquenessTestingService $uniquenessService */
        $uniquenessService = resolve(UniquenessTestingService::class);
        foreach ($texts as $newText) {
            if(strlen($newText) < 100) { continue; }
            $unique = !$uniquenessService->hasDuplicates($newText, $existingTexts, 25, $stopWords);
            if($unique) {
                $nonDuplicateTexts[] = $newText;
                $existingTexts[] = $newText;
            }
        }

        $this->info(sprintf('%s of %s generated texts were unique', count($nonDuplicateTexts), $qty));

        $payload = [];
        foreach ($nonDuplicateTexts as $nonDuplicateText) {
            $payload[] = [
                'text' => $nonDuplicateText,
                'type' => 'generated',
                'source_article' => $sourceArticle,
                'piece' => $piece
            ];
        }

        ContentChunk::query()
            ->insert($payload);

        return 0;
    }

    private function getExistingTexts(string $sourceArticle, $piece)
    {
        $chunks = ContentChunk::query()
            ->where('source_article', '=', $sourceArticle)
            ->where('piece', '=', $piece)
            ->get();

        return $chunks->pluck('text')->toArray();
    }
}
