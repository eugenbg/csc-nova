<?php

namespace App\Console\Commands;

use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;

class ChooseGeneratedPieces extends ChoosePieces
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'choose-generated-pieces {keyword_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @param UniquenessTestingService $uniquenessService
     */
    public function __construct(UniquenessTestingService $uniquenessService)
    {
        parent::__construct($uniquenessService);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keywordId = $this->argument('keyword_id');
        /** @var Keyword $keyword */
        $keyword = Keyword::query()->find($keywordId);
        $chosenGeneratedPiecesIds = [];
        $allDistances = [];
        foreach ($keyword->pieces as $piece) {
            $allDistances[$piece->id] = [];
            $distances = [];
            foreach ($piece->generatedPieces as $generatedPiece) {
                $distance = $this->getDistance($piece, $generatedPiece);
                $distances[$generatedPiece->id] = $distance;
                $allDistances[$piece->id][$generatedPiece->id] = $distance;
            }

            if(count($distances)) {
                $chosenGeneratedPiecesIds[] = array_search(min($distances), $distances);
            }
        }

        $chosenGeneratedPieces = GeneratedPiece::query()
            ->whereIn('id', $chosenGeneratedPiecesIds)
            ->get();

        /** @var GeneratedPiece $chosenGeneratedPiece */
        foreach ($chosenGeneratedPieces as $chosenGeneratedPiece) {
            $this->info('HEADING:' . $chosenGeneratedPiece->heading);
            $this->info('DISTANCE: ' . $allDistances[$chosenGeneratedPiece->piece->id][$chosenGeneratedPiece->id]);
            $this->info('CONTENT: ' . $chosenGeneratedPiece->content);
            $this->info('ORIGINAL CONTENT: ' . $chosenGeneratedPiece->piece->content);
        }
    }
}
