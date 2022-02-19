<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\ChinaUniversity;
use App\Models\Spell;
use App\Services\TextGenerationService;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class RewriteArticleForUni extends Command
{
    const DEFAULT_ARTICLE_SOURCE = 'china-uni-flat-2';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'article:rewrite {uniId?} {sourceArticle?}';

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
        $uniId = $this->argument('uniId');
        if($uniId) {
            $uni = ChinaUniversity::find($uniId);
        } else {
            $uni = ChinaUniversity::query()
                ->where('segment', '=', 'unique')
                ->where('generated', '=', '')
                ->orderBy('id')
                ->first();
        }

        $sourceArticleSlug = $this->argument('sourceArticle')
            ? $this->argument('sourceArticle')
            : self::DEFAULT_ARTICLE_SOURCE;

        $sourceArticle = Post::query()->where('slug', '=', $sourceArticleSlug)->first();
        $content = str_replace('%uni', $uni->name, $sourceArticle->content);
        $matches = [];
        preg_match_all('/<\/h2>(?s).*?<h2>/', $content, $matches);
        $crawler = new Crawler($sourceArticle->content);
        $h2Html = [];
        $crawler->filter('h2')->each(function($h2) use(&$h2Html) {
            $h2Html[] = $h2->outerHtml();
        });

        $pattern = '(' . str_replace('/', '\/', implode('|', $h2Html)) . ')';
        $pieces = preg_split($pattern, $content);
        $spell = Spell::find(5);

        $rewrittenArticle = [];
        foreach ($pieces as $key => $paragraph) {
            if($key == 0) { continue; }
            $paragraph = str_replace(['<h2>', '</h2>'], '', $paragraph);
            $paragraph = str_replace(['</li>'], "\n", $paragraph);
            $paragraph = str_replace(['<li>'], '-', $paragraph);
            $paragraph = strip_tags($paragraph);
            $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
            $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
            $paragraph = str_replace(["\n\n", "\r\n\r\n"], "\n", $paragraph);
            $paragraph = str_replace(["-\r\n", "-\n"], "-", $paragraph);
            $paragraph = ltrim(rtrim($paragraph));
            $rewritten = TextGenerationService::generate($paragraph, $spell);
            $rewrittenArticle[$h2Html[$key]] = $rewritten;
        }

        $uni->generated = json_encode($rewrittenArticle);
        $uni->save();

        return 0;
    }
}
