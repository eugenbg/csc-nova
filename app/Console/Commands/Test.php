<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\ChinaUniversity;
use App\Models\ContentChunk;
use App\Models\Page;
use App\Services\HeadingGenerationService;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use OptimistDigital\MenuBuilder\Models\MenuItem;

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
        $piece = \App\Models\GeneratedPiece::query()->find(25);



        /** @var HeadingGenerationService $s */
        $s = resolve(HeadingGenerationService::class);
        $s->generateHeading($piece);
    }

}
