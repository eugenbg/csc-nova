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
    protected $signature = 'mother {--keyword=} {--from=} {--to=}';

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
        $from = $this->option('from');
        $to = $this->option('to');
        if($id) {
            $keywords = collect([Keyword::find($id)]);
        } else {
            $builder = Keyword::query();
            if($from && $to) {
                $builder->whereBetween('id', [$from, $to]);
            }

            $keywords = $builder->get();
        }

        $this->info(sprintf('got %s keywords, starting...', $keywords->count()));

        $command = $this;
        foreach ($keywords as $key => $keyword) {
            $this->service->generateArticle($keyword, $key, function(...$args) use ($command) {
                $command->info(sprintf(...$args));
            });
        }

        return 0;
    }
}
