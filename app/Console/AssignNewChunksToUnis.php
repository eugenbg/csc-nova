<?php

namespace App\Console\Commands;

use App\Models\ChinaUniversity;
use App\Models\ContentChunk;
use App\Services\ArticleText2HtmlService;
use App\Services\TextGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class AssignNewChunksToUnis extends Command
{
    /**
     * взять универы и назначить им новые кусочки контента
     *
     * @var string
     */
    protected $signature = 'uni:assign-chunks {uniIds}';

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

    public function handle()
    {
        $uniIds = explode(',', $this->argument('uniIds'));

        $unis = ChinaUniversity::query()
            ->whereIn('id', $uniIds)
            ->get();

        //delete old content chunks
        ContentChunk::query()
            ->whereIn('uni_id', $uniIds)
            ->delete();

        //find chunks that are not assigned to unis
        $contentChunks = ContentChunk::query()
            ->where('uni_id', '=', 0)
            ->get();

        $chunksByPiece = [];
        foreach ($contentChunks as $contentChunk) {
            $chunksByPiece[$contentChunk->piece][] = $contentChunk;
        }

        foreach ($unis as $uni) {
            for ($i = 1; $i <= 6; $i++) {
                $chunk = array_pop($chunksByPiece[$i]);
                $chunk->uni_id = $uni->id;
                $chunk->save();
            }

            $this->info($uni->name);
        }
    }
}
