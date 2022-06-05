<?php


namespace App\Http\Controllers;


use App\Models\ChinaUniImage;
use App\Models\ChinaUniversity;
use App\Models\Post;
use Illuminate\Http\Request;

class HomeController
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home(Request $request)
    {
        $posts = Post::query()
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('home', [
            'posts' => $posts,
        ]);
    }

}
