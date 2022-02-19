<?php

namespace App\Console\Commands;

use App\Models\ChinaUniversity;
use App\Models\Spell;
use App\Services\TextGenerationService;
use Illuminate\Console\Command;

class AddChinaUniAbbreviations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abbr';

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
        $spell = Spell::query()->find(7);
        $collection = ChinaUniversity::get();
        foreach ($collection as $uni) {
            $name = rtrim(ltrim($uni->name));
            $abbr = str_replace(')', '', rtrim(ltrim(TextGenerationService::generate($name, $spell))));
            $uni->abbr = $abbr;
            $uni->save();
        }
    }
}
