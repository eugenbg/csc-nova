<?php


namespace App\Http\Controllers;


use App\Models\ChinaUniImage;
use App\Models\ChinaUniversity;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController
{
    const MAIN_ARTICLE_SLUG = 'csc-china-scholarship-council-scholarships';

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home(Request $request)
    {
        $mainPost = Post::query()
            ->where('slug', '=', self::MAIN_ARTICLE_SLUG)
            ->first();

        $posts = Post::query()
            ->whereNotNull('image')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('home', [
            'mainPost' => $mainPost,
            'posts' => $posts,
            'unis' => ChinaUniversity::query()->get()
        ]);
    }

    public function ft()
    {
        $pairs = DB::table('ft_pieces')
            ->where('unique', '<', 6)
            ->get();

        return view('test', [
            'pairs' => $pairs,
        ]);
    }

    public function choose($id)
    {
        DB::table('ft_pieces')
            ->where('id', '=', $id)
            ->update(['chosen' => 1]);

        return 1;
    }
}
