<?php

namespace App\Console\Commands;

use App\Models\ChinaUniversity;
use App\Models\ContentChunk;
use Illuminate\Console\Command;

class UniChangePiece extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uni:change-piece {uniId} {piece}';

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
        $piece = $this->argument('piece');

        $newChunk = ContentChunk::query()
            ->where('uni_id', '=', 0)
            ->where('piece', '=', $piece)
            ->first();

        if($newChunk) {
            ContentChunk::query()
                ->where('uni_id', '=', $uniId)
                ->where('piece', '=', $piece)
                ->delete();

            $newChunk->uni_id = $uniId;
            $newChunk->save();
            \Artisan::call('uni:compose', ['uniIds' => $uniId]);
        } else {
            $this->error('no available pieces, generate more');
        }
    }
}
