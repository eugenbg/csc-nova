<?php


namespace App\Http\Controllers;

use App\Models\ChinaUniImage;
use App\Models\ChinaUniversity;
use App\Models\Post;

class ChinaUniversityController
{

    public function getUni($uniId, $request)
    {
        $uni = ChinaUniversity::query()->find($uniId);

        $campusImages = [];
        $dormImages = [];
        $missFirstCampusPhoto = true;
        foreach ($uni->images as $image) {
            if($image->type == ChinaUniImage::TYPE_CAMPUS && $missFirstCampusPhoto) {
                $missFirstCampusPhoto = false;
                continue;
            }

            if($image->type == ChinaUniImage::TYPE_CAMPUS && !$missFirstCampusPhoto) {
                $campusImages[] = $image;
            }
            if($image->type == ChinaUniImage::TYPE_DORM) {
                $dormImages[] = $image;
            }
        }

        $unique = $uni->segment == 'unique';

        if($unique) {
            $content = $uni->generated_html;
        } else {
            $article = Post::query()->where('slug', '=', 'china-uni-flat')->first();
            $content = str_replace('%uni', $uni->name, $article->content);
        }

        $links = $uni->links()
            ->with(['linkedUni', 'linkedUni.image'])
            ->get();

        return view('china-uni-unique', [
            'content' => $content,
            'uni' => $uni,
            'links' => $links,
            'image' => $uni->image ?? null,
            'programs' => $uni->getPrograms(),
            'campusImages' => $campusImages,
            'dormImages' => $dormImages,
            'dorms' => $uni->dorms,
            'scholarships' => $uni->scholarships,
        ]);

    }
}
