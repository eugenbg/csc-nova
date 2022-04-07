<?php

namespace App\Console\Commands;

use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Spell;
use App\Services\TextGenerationService;
use Illuminate\Console\Command;

class RewritePieces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rewrite:pieces {keyword?}';

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
        $keywordId = $this->argument('keyword');

        $builder = Keyword::query()
            ->has('pieces')
            ->limit(1);

        if($keywordId) {
            $builder->whereId($keywordId);
        }

        foreach ($builder->get() as $keyword) {
            $this->rewritePieces($keyword);
        }
    }
}
