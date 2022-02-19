<?php

namespace App\Console\Commands;

use App\Models\ChinaUniversity;
use App\Models\ContentChunk;
use App\Services\ArticleText2HtmlService;
use App\Services\TextGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ComposeUniArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uni:compose {uniIds}';

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
        $uniIds = explode(',', $this->argument('uniIds'));
        $this->compose($uniIds);
    }

    private function compose($uniIds = [])
    {
        $h2Html = [
            1 => '<h2>Facts To Know</h2>',
            2 => '<h2>Where To Start</h2>',
            3 => '<h2>Who Can Apply</h2>',
            4 => '<h2>How To Fill The Application Form</h2>',
            5 => '<h2>How To Increase Your Chances</h2>',
            6 => '<h2>Time Frame</h2>',
        ];

        $builder = ChinaUniversity::query();
        if(count($uniIds)) {
            $builder->whereIn('id', $uniIds);
        }

        $unis = $builder->get();

        foreach ($unis as $uni) {
            $chunks = ContentChunk::query()->where('uni_id', '=', $uni->id)
                ->get()
                ->sortBy('piece')
                ->toArray();

            $texts = Arr::pluck($chunks, 'text');

            $rewrittenArticle = [];
            foreach ($texts as $key => $paragraph) {
                $i = $key + 1;
                $paragraph = str_replace(['<h2>', '</h2>'], '', $paragraph);
                $paragraph = str_replace(['</li>'], "\n", $paragraph);
                $paragraph = str_replace(['<li>'], '-', $paragraph);
                $paragraph = strip_tags($paragraph);
                $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
                $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
                $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
                $paragraph = str_replace(["-\r\n", "-\n"], "-", $paragraph);
                $paragraph = ltrim(rtrim($paragraph));
                $rewrittenArticle[$h2Html[$i]] = $paragraph;
            }

            $uni->generated = json_encode($rewrittenArticle);
            $uni->save();
        }

        $this->info('converting to html');

        /** @var ArticleText2HtmlService $service */
        $serviceTextToHtml = resolve(ArticleText2HtmlService::class);
        foreach ($unis as $uni) {
            $serviceTextToHtml->convert($uni);
        }
    }
}
