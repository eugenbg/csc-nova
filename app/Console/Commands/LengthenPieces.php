<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\ArticleGenerationService;
use App\Services\FinalizingService;
use Illuminate\Console\Command;

class LengthenPieces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lengthen {--keyword=}';

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
        $keywordId = $this->option('keyword');
        /** @var ArticleGenerationService $service */
        FinalizingService::lengthenPost(Keyword::query()->findOrFail($keywordId));
    }
}
