<?php

namespace App\Console\Commands;

use App\Models\DonorPage;
use Illuminate\Console\Command;

class MapCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $map = [
        'acceptance' => 'University Acceptance Rates',
        'scholarships in' => 'Scholarships By Country',
        'doctorate' => 'PHD Scholarships',
        'engineering scholarships' => 'Engineering Scholarships',
        'internships' => 'Internships',
        'european scholarships' => 'European Scholarships',
        'essay' => 'Essay Help',
        'researcher' => 'Scientific Research',
        'courses' => 'Courses',
        'ukraine' => 'Scholarships By Country',
        'Japanese' => 'Scholarships By Country',
        'Kuwaiti' => 'Scholarships By Country',
        'Netherlands' => 'Scholarships By Country',
        'Bangkok' => 'Scholarships By Country',
        'Saudi Arab' => 'Scholarships By Country',
        'Czech' => 'Scholarships By Country',
    ];

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
        $pages = DonorPage::query()
            ->where('donor_categories', 'like', '%[%')
            ->get();

        $allCats = [];
        foreach ($pages as $page) {
            $allCats = array_merge($allCats, $page->donor_categories);
        }

        $categories = array_unique($allCats);
        $i = 0;
        foreach ($categories as $category) {
            $i++;
            $mapped = '';
            foreach ($this->map as $from => $to) {
                if(strpos(mb_strtolower($category), mb_strtolower($from)) !== false) {
                    $mapped = $to;
                }
            }
            if(!$mapped) {
                $mapped = 'Miscellaneous';
            }

            $this->info($i . '. ' . $category . ': ' . $mapped);

            DonorPage::query()
                ->where('donor_categories', 'like', '%' . $category . '%')
                ->update(['local_category' => $mapped]);
        }
    }
}
