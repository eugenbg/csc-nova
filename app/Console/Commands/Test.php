<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\ChinaUniversity;
use App\Models\ContentChunk;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use OptimistDigital\MenuBuilder\Models\MenuItem;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var int
     */
    private $i;

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
        $cats = Category::all();
        $i = 1;
        foreach ($cats as $cat) {
            if($cat->show_in_menu) {
                $model = new MenuItem();
                $model->menu_id = 1;
                $model->name = $cat->title;
                $model->value = $cat->slug;
                $model->locale = 'en_US';
                $model->class = 'OptimistDigital\MenuBuilder\MenuItemTypes\MenuItemStaticURLType';
                $model->target = '_self';
                $model->enabled = 1;
                $model->parent_id = 12;
                $model->order = $i;
                $model->save();
                $i++;
            }
        }
    }

    private function comparePiecesOfOneArticleToAllOthers($uniId, $pieceNumber = 1)
    {
        $uni = ChinaUniversity::find($uniId);
        $otherUnis = ChinaUniversity::query()
            ->where('id', '!=', $uniId)
            ->where('generated', '!=', '')
            ->get();

        foreach ($otherUnis as $otherUni) {
            /** @var UniquenessTestingService $service */
            $service = resolve(UniquenessTestingService::class);
            $result = $service->run(
                $uni->getGeneratedTextPiece($pieceNumber),
                $otherUni->getGeneratedTextPiece($pieceNumber),
                3
            );

            if($result > 30) {
                $this->i++;
                $this->error(sprintf(
                    'uni %s and uni %s, peace %s, matching percent %s',
                    $uni->name,
                    $otherUni->name,
                    1,
                    $result
                ));
            }
        }

        $this->info($this->i);
    }

    private function compareAllUniAgainstAllUnis($pieceNumber)
    {
        $this->i = 0;

        $unis = ChinaUniversity::query()
            ->where('generated', '!=', '')
            ->get();

        foreach ($unis as $uni) {
            $this->comparePiecesOfOneArticleToAllOthers($uni->id, $pieceNumber);
        }
    }

    private function compareContentChunks($pieceNumber)
    {
        $contentChunks = ContentChunk::query()
            ->where('piece', '=', $pieceNumber)
            ->get();
        $service = resolve(UniquenessTestingService::class);
        $service->setStopWords(['Central South University', 'CSU', 'CSCU']);

        $duplicates = [];
        foreach ($contentChunks as $contentChunk) {
            foreach ($contentChunks->except([$contentChunk->id, 1]) as $otherChunk) {
                /** @var UniquenessTestingService $service */
                $result = $service->run(
                    $contentChunk->text,
                    $otherChunk->text,
                    3
                );

                if($result > 20) {
                    $this->info(sprintf('chunk %s and chunk %s are the %s percent the same', $contentChunk->id, $otherChunk->id, $result));
                    $duplicates[] = $otherChunk->id;
                }
            }
        }

        $duplicates = array_unique($duplicates);
        $this->info(sprintf('deleting %s duplicates', count($duplicates)));

        ContentChunk::query()
            ->whereIn('id', $duplicates)
            ->delete();
    }
}
