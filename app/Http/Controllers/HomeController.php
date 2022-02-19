<?php


namespace App\Http\Controllers;


use App\Models\Post;
use App\Models\SmallPost;
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
            ->whereNotNull('image')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('home', [
            'posts' => $posts,
            'smallPosts' => []
        ]);
    }

}
