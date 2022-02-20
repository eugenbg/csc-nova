<?php

namespace App\Console\Commands;

use App\Models\ChinaUniLink;
use App\Models\ChinaUniversity;
use App\Models\Spell;
use App\Services\TextGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class GenerateLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'links';

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
        ChinaUniLink::query()->truncate();
        $unis = ChinaUniversity::query()->get();
        $ids = $unis->pluck('id')->toArray();
        foreach ($unis as $uni) {
            $payload = [];
            $otherIds = $ids;
            if (($key = array_search($uni->id, $otherIds)) !== false) {
                unset($otherIds[$key]);
            }

            $linkedUnis = array_rand($otherIds, 6);
            foreach ($linkedUnis as $linkedUniKey) {
                $payload[] = [
                    'uni_id' => $uni->id,
                    'linked_uni_id' => $otherIds[$linkedUniKey],
                ];
            }

            ChinaUniLink::query()->insert($payload);
            $this->info($uni->id);
        }
    }
}
