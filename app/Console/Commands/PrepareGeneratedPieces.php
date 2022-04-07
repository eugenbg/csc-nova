<?php

namespace App\Console\Commands;

use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Piece;
use App\Services\TextGenerationService;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class PrepareGeneratedPieces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prepare-generated-pieces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $uniquenessService;

    private $badCharacters = ['{', '}', '/', '\\'];

    /**
     * Create a new command instance.
     *
     * @param UniquenessTestingService $uniquenessService
     */
    public function __construct(UniquenessTestingService $uniquenessService)
    {
        parent::__construct();
        $this->uniquenessService = $uniquenessService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keywords = Keyword::query()
            ->where('generated_pieces_cleaned','=', 0)
            ->limit(1)
            ->get();

        foreach ($keywords as $keyword) {
            $this->cleanPiecesForKeyword($keyword);
        }
    }

    private function cleanPiecesForKeyword(Keyword $keyword)
    {
        $start = now();
        $deleteIds = [];
        $good = new Collection();
        $test = [];


        foreach ($keyword->generatedPieces as $generatedPiece) {
            $words = explode(' ', $generatedPiece->content);
            if(count($words) < 50) {
                $deleteIds[] = $generatedPiece->id;
                $test[$generatedPiece->original_heading] = $generatedPiece->content;
                continue;
            }

            if($this->strpos_arr($generatedPiece->content, $this->badCharacters)) {
                $deleteIds[] = $generatedPiece->id;
                continue;
            }

            $good->add($generatedPiece);
        }

        $good = $good->keyBy('id');
        $goodUnique = new Collection();

        /** @var GeneratedPiece $generatedPiece */
        foreach ($good as $generatedPiece) {
            $existingTexts = $good->except([$generatedPiece->id])->pluck('content')->toArray();
            $existingTexts[] = $generatedPiece->piece->content;
            $unique = !$this->uniquenessService->hasDuplicates($generatedPiece->content, $existingTexts, 15, []);
            if($unique) {
                $goodUnique->add($generatedPiece);
            }
        }

        GeneratedPiece::query()
            ->whereNotIn('id', $goodUnique->pluck('id')->toArray())
            ->where('keyword_id', '=', $keyword->id)
            ->delete();

        $payload = [];
        foreach ($goodUnique as $generatedPiece) {
            if(!$generatedPiece->embedding) {
                $payload[$generatedPiece->id] = $generatedPiece->content;
            }
        }

        if(count($payload)) {
            $startRequest = now();
            $result = TextGenerationService::embeddings($payload);
            $this->info('request time ' . now()->diffInMilliseconds($startRequest) / 1000);

            foreach ($result as $id => $vector) {
                GeneratedPiece::query()
                    ->where('id', '=', $id)
                    ->update(['embedding' => json_encode($vector)]);
            }
        }

        $this->info('overall time ' . now()->diffInMilliseconds($start) / 1000);
    }

    private function strpos_arr($haystack, $needle) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $what) {
            if(($pos = strpos($haystack, $what))!==false) return $pos;
        }
        return false;
    }
}
