<?php

namespace App\Http\Controllers;

use App\Models\GeneratedPost;
use App\Models\Post;

class PostController {


    /**
     * @param Post|GeneratedPost $post
     * @param $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPost($post, $request)
    {
        return view('post', ['post' => $post]);
    }

}
