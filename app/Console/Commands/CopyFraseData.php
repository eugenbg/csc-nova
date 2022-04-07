<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Models\Piece;
use App\Models\Serp;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CopyFraseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy-frase';

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
        Keyword::query()->truncate();
        Piece::query()->truncate();
        Serp::query()->truncate();

        $keywords = DB::connection('frase')
            ->table('keywords')
            ->select('*')
            ->get();

        $i = 0;
        foreach ($keywords as $keyword) {
            $i++;
            $serps = DB::connection('frase')
                ->table('serps')
                ->select('*')
                ->where('keyword_id', '=', $keyword->id)
                ->get();

            $pieces = DB::connection('frase')
                ->table('pieces')
                ->select('*')
                ->where('keyword_id', '=', $keyword->id)
                ->get();

            if($serps->count() && $pieces->count()) {
                $this->copyKeywordData($keyword, $serps, $pieces);
                $this->info('copied data for ' . $keyword->keyword);
            }

            if($i == 5) {
                die();
            }
        }

        return 0;
    }

    private function copyKeywordData($keyword, Collection $serps, Collection $pieces)
    {
        $data = (array) $keyword;
        $keywordModel = new Keyword;
        $keywordModel->fill($data)->save();
        $groupedPieces = $pieces->groupBy('serp_id');

        foreach ($serps as $serp) {
            $serpModel = new Serp();
            $data = (array) $serp;
            $serpModel->fill($data);
            $serpModel->keyword_id = $keywordModel->id;
            $serpModel->save();

            $piecesPayload = [];
            $serpPieces = $groupedPieces->get($serp->id);
            if($serpPieces && $serpPieces->count() > 1) {
                foreach ($groupedPieces->get($serp->id) as $piece) {
                    $piecesPayload[] = [
                        'keyword_id' => $keywordModel->id,
                        'serp_id' => $serpModel->id,
                        'heading' => $piece->heading,
                        'position' => $piece->position,
                        'content' => $piece->content,
                    ];
                }

                Piece::query()->insert($piecesPayload);
            }
        }
    }
}
