<?php

namespace App\Console\Commands;

use App\Helper;
use App\Models\Category;
use App\Models\DonorPage;
use App\Models\GeneratedPiece;
use App\Models\Serp;
use App\Models\Spell;
use App\Services\ArticleGenerationService;
use App\Services\HeadingGenerationService;
use App\Services\TextGenerationService;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var int
     */
    private $i;

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

        /** @var GeneratedPiece[] $generatedPieces */
        $generatedPieces = GeneratedPiece::query()
            ->where('generated_post_id', '=', 220)
            ->get()
            ->toArray();

        /** @var DonorPage $page */
        $page = DonorPage::query()->find(6);
        foreach (array_values($page->content_pieces) as $key => $group) {
            $text = '';
            foreach ($group as $contentPiece) {
                if(!in_array($contentPiece['type'], ['ul', 'ol', 'h1', 'h2', 'h3'])) {
                    $text .= "\n" . $contentPiece['content'];
                }
            }

            $gp = $generatedPieces[$key];
            $gc = $gp['content'];

            $this->replaceBiggestShinglesInGeneratedText($key, $text, $gc);
        }
    }

    public function replaceBiggestShinglesInGeneratedText($i, $originalText, $generatedText)
    {
        $originalGeneratedText = $generatedText;
        $this->info('peice number ' . $i);
        /** @var UniquenessTestingService $s */
        $s = resolve(UniquenessTestingService::class);
        $shingles = $s->getIntersectingShingles($originalText, $generatedText, 6);
        $sentences = preg_split('/(?<!Mr.|Mrs.|Ms.|Dr.|St.|\s[A-Z].|Ph.D.|Ph.)(?<=[.?!;])\s+/', $generatedText, -1, PREG_SPLIT_NO_EMPTY);

        $sentencesForRewrite = [];
        $added = [];
        foreach ($sentences as $sentence) {
            foreach ($shingles as $shingle) {
                if(strpos($sentence, $shingle) && !in_array($sentence, $added)) {
                    $added[] = $sentence;
                    $sentencesForRewrite[] = [
                        'size' => Helper::words($shingle),
                        'shingle' => $shingle,
                        'text' => $sentence
                    ];
                }
            }
        }

        $winners = [];
        $replacementSentences = [];
        $rewriteSpell = Spell::query()->find(16);
        foreach ($sentencesForRewrite as $payload) {
            $rewriteSpell->temperature = 0.8;
            $rewrites = TextGenerationService::generate($payload['text'], $rewriteSpell, 5);
            $rewritesByMaxShingleLength = [];
            foreach ($rewrites as $rewrite) {
                ['size' => $size, 'max_size_shingles' => $maxSizeShingles] = $s->getMaxIntersectingShingleSize($originalText, $rewrite);
                $rewritesByMaxShingleLength[] = [
                    'max_size_shingles' => $maxSizeShingles,
                    'shingle_length' => $size,
                    'sentence' => $rewrite,
                ];
            }

            $sorted = array_values(Arr::sort($rewritesByMaxShingleLength, 'shingle_length'));
            $winner = $sorted[0];
            $winners[] = [
                'was' => $payload,
                'winner' => $winner,
            ];

            if($winner['shingle_length'] < $payload['size']) {
                $replacementSentences[] = [
                    'from' => $payload['text'],
                    'to' => $winner['sentence']
                ];
            }
        }

        foreach ($winners as $winner) {
            $this->info(sprintf(
                'original sentence: %s',
                $winner['was']['text']
            ));

            $this->info(sprintf(
                'biggest intersecting shingle: "%s", size: %s',
                $winner['was']['shingle'],
                $winner['was']['size'],
            ));

            $this->info('============');

            $this->info(sprintf(
                'rewritten sentence: "%s"',
                $winner['winner']['sentence'],
            ));

            $this->info(sprintf(
                'biggest intersecting shingle size: %s',
                $winner['winner']['shingle_length'],
            ));

            $this->info('============');
        }

        foreach ($replacementSentences as $replacementSentence) {
            $generatedText = str_replace($replacementSentence['from'], $replacementSentence['to'], $generatedText);
        }

        $this->info('ORIGINAL TEXT: ' . $originalGeneratedText);
        $this->info('REWRITTEN TEXT: ' . $generatedText);

        return $generatedText;
    }
}
