<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\ArticleGenerationService;
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
    /**
     * @var ArticleGenerationService
     */
    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ArticleGenerationService $service
    )
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->option('keyword');
        if($id) {
            $keywords = [Keyword::find($id)];
        } else {
            $keywords = Keyword::all();
        }
        $command = $this;
        foreach ($keywords as $key => $keyword) {
            $this->service->generateArticle($keyword, $key, function(...$args) use ($command) {
                $command->info(sprintf(...$args));
            });

            die();
        }

        return 0;
    }
}
