<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;

class ParsePlacesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'places {--keyword=}';

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
        $builder = Keyword::query()
            ->has('generatedPost');

        if($id = $this->option('keyword')) {
            $builder->where('id', '=', $id);
        } else {
            $builder->whereNull('additional_data');
        }

        $keywords = $builder->get();

        /** @var Keyword $keyword */
        foreach ($keywords as $keyword) {
            $this->info('parsing google places data for keyword ' . $keyword->keyword);
            $result = GooglePlacesService::saveKeywordData($keyword);
            if($result) {
                $this->info('parsed successfully for keyword ' . $keyword->keyword);
            } else {
                $this->error('could not find data for keyword ' . $keyword->keyword);
            }
        }
    }
}
