<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Models\Piece;
use App\Services\TextGenerationService;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class PreparePieces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prepare-pieces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $uniquenessService;

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
            ->where('pieces_cleaned','=', 0)
            ->limit(1)
            ->get();

        foreach ($keywords as $keyword) {
            $this->cleanPiecesForKeyword($keyword);
        }
    }
}
