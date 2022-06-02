<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\GeneratedPiece;
use App\Models\Serp;
use App\Services\ArticleGenerationService;
use App\Services\HeadingGenerationService;
use Illuminate\Console\Command;

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
        $k = \App\Models\Keyword::query()->find(13);
        /** @var ArticleGenerationService $s */
        $s = resolve(\App\Services\ArticleGenerationService::class);

        //$s = resolve(\App\Services\HeadingGenerationService::class);

/*        $gp = GeneratedPiece::query()->find(163);
        $s->generateHeading($gp);*/

        //$category = Category::query()->find(15);

        $serp = Serp::query()->find(214);
        $s->generateHeadings($serp);
    }

}
