<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\ArticleGenerationService;
use App\Services\SerpScoringService;
use Illuminate\Console\Command;

class GetBestSerpByKeyword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'choose-serp {--keyword=}';

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
        $service = resolve(ArticleGenerationService::class);
        $service->getBestSerpByKeyword(Keyword::query()->findOrFail($keywordId));
    }
}
