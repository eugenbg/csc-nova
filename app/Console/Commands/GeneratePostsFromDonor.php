<?php

namespace App\Console\Commands;

use App\Helper;
use App\Models\Category;
use App\Models\DonorPage;
use App\Models\GeneratedPiece;
use App\Models\GeneratedPost;
use App\Models\Spell;
use App\Services\EmbeddingDistanceService;
use App\Services\TextGenerationService;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class GeneratePostsFromDonor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen {--donor_id=}';

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
    private $uService;
    private $minUniqueness = 20;

    private $rewriteSpell;
    private $longSummarySpell;
    private $shortSummarySpell;
    private $currentSpell;
    private $triesBeforeGiveUp = 2;
    private $rewriteLog = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UniquenessTestingService $uService)
    {
        parent::__construct();
        $this->uService = $uService;
        $this->rewriteSpell = Spell::query()->find(16);
        $this->longSummarySpell = Spell::query()->find(15);
        $this->shortSummarySpell = Spell::query()->find(14);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $donorId = $this->option('donor_id');

        $builder = DonorPage::query()
            ->whereNotNull('local_category')
            ->whereNotNull('content_pieces');

        if($donorId) {
            $builder->where('id' , '=', $donorId);
        }

        $donors = $builder->get();
        foreach ($donors as $donor) {
            $this->generatePostFromDonor($donor);
        }
    }

    private function generatePostFromDonor(DonorPage $donor)
    {
        $gPost = new GeneratedPost();
        $gPost->title = $this->generateTitle($donor->keywords[0]);
        $gPost->meta_title = $gPost->title;
        $gPost->category_id = $this->getCategoryId($donor);
        $gPost->source_url = $donor->url;
        $gPost->save();

        $this->generatePieces($donor, $gPost);
    }

    /**
     * TODO implement
     * @param $keyword
     * @return mixed
     */
    private function generateTitle($keyword)
    {
        return $keyword;
    }

    private function getCategoryId(DonorPage $donor)
    {
        $category = Category::query()
            ->where('title', '=', $donor->local_category)
            ->first();

        if(!$category) {
            $category = new Category();
            $category->title = $donor->local_category;
            $category->save();
        }

        return $category->id;
    }


    private function rewrite($text, $existingRewrites = [])
    {
        if ($this->level >= $this->triesBeforeGiveUp) {
            $this->level = 0;
            if(!$this->switchCurrentSpell()) {
                $a = 0;
                $a++;
                return null;
            }
        }

        $this->level++;
        $rewrites = TextGenerationService::generate($text, $this->currentSpell, 4);
        $rewrites = array_merge($existingRewrites, $rewrites);

        $filtered = [];
        foreach ($rewrites as $rewrite) {
            $rewrite = trim($rewrite);
            if(!strlen($rewrite)) {
                continue;
            }

            $resembling = $this->uService->run($text, $rewrite, 3);
            $this->rewriteLog[] = [
                'type' => $this->getType(),
                'resembling' => $resembling,
                'content' => $rewrite
            ];

            if ($resembling < $this->minUniqueness) {
                $filtered[] = $rewrite;
            }
        }

        if(count($filtered) > 1) {
            $embeddings = TextGenerationService::embeddings($filtered);
            [$originalEmbedding] = TextGenerationService::embeddings([$text]);

            $distances = [];
            foreach ($embeddings as $key => $embedding) {
                $distances[$key] = EmbeddingDistanceService::getDistance($originalEmbedding, $embedding);
            }

            $index = array_search(min($distances), $distances);
            return $filtered[$index];
        } elseif (count($filtered) == 1) {
            return array_pop($filtered);
        }

        $this->info('going for an extra round');
        return $this->rewrite($text);
    }

    private function generatePieces(DonorPage $donor, GeneratedPost $gPost)
    {
        $contentGroups = array_values($donor->content_pieces);
        foreach ($contentGroups as $key => $group) {
            $this->info('starting generation for piece #' . $key );
            $this->resetCurrentSpell();
            $this->level = 0;

            $text = '';
            foreach ($group as $contentPiece) {
                if(!in_array($contentPiece['type'], ['ul', 'ol', 'h1', 'h2', 'h3'])) {
                    $text .= "\n" . $contentPiece['content'];
                }
            }

            $rewritten = $this->rewrite($text);
            if($rewritten) {
                $this->replaceBiggestShinglesInGeneratedText($key, $text, $rewritten, 6);
                $gp = new GeneratedPiece();
                $gp->generated_post_id = $gPost->id;
                $gp->content = $rewritten;
                $gp->original_content = $text;
                $gp->chosen = 1;
                $gp->type = $this->getType();
                $gp->save();
                $this->info('saved piece #' . $key);
            } else {
                $this->error('missing piece');
                $this->error($text);
            }
        }
    }

    public function switchCurrentSpell()
    {
        if($this->currentSpell === $this->rewriteSpell) {
            $this->info('switching to long summary');
            $this->currentSpell = $this->longSummarySpell;
            return $this->currentSpell;
        }

        if($this->currentSpell === $this->longSummarySpell) {
            $this->info('switching to long short summary');
            $this->currentSpell = $this->shortSummarySpell;
            return $this->currentSpell;
        }
    }

    public function resetCurrentSpell()
    {
        $this->info('resetting to rewrite');
        $this->currentSpell = $this->rewriteSpell;
        return $this->currentSpell;
    }

    private function getType()
    {
        if($this->currentSpell === $this->rewriteSpell) {
            return 'rewrite';
        }

        if($this->currentSpell === $this->longSummarySpell) {
            return 'long_summary';
        }

        if($this->currentSpell === $this->shortSummarySpell) {
            return 'short_summary';
        }
    }
    public function replaceBiggestShinglesInGeneratedText($i, $originalText, $generatedText, $targetedShingleSize = 6)
    {
        $originalGeneratedText = $generatedText;
        /** @var UniquenessTestingService $s */
        $s = resolve(UniquenessTestingService::class);
        $shingles = $s->getIntersectingShingles($originalText, $generatedText, $targetedShingleSize);
        if(!count($shingles)) {
            return;
        }

        $this->info('peice number ' . $i . ' || REWRITING SENTENCES WITH SHINGLES OF SIZE MORE THAN ' . $targetedShingleSize);
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
                    'sentence' => trim($rewrite),
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
