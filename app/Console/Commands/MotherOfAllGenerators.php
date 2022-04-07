<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\ChoosePiecesService;
use App\Services\PreparePiecesService;
use Illuminate\Console\Command;

class MotherOfAllGenerators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mother {--keyword=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var PreparePiecesService
     */
    private $preparePiecesService;
    /**
     * @var ChoosePiecesService
     */
    private $choosePiecesService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        PreparePiecesService $preparePiecesService,
        ChoosePiecesService $choosePiecesService
    )
    {
        parent::__construct();
        $this->preparePiecesService = $preparePiecesService;
        $this->choosePiecesService = $choosePiecesService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->option('keyword');
        $keyword = Keyword::find($id);

        $this->info(sprintf('STARTED GENERATION for keyword %s', $keyword->keyword));
        $this->info(sprintf('cleaning pieces for keyword %s', $keyword->keyword));
        $this->info(sprintf('%s pieces BEFORE cleaning', $keyword->pieces->count()));

        $this->preparePiecesService->cleanPiecesForKeyword($keyword);

        $keyword->refresh();
        $this->info(sprintf('%s pieces AFTER cleaning', $keyword->pieces->count()));

        $this->info(sprintf('choosing pieces for rewrite for keyword %s', $keyword->keyword));
        $this->choosePiecesService->choosePiecesByBestArticle($keyword);


        return 0;
    }
}
