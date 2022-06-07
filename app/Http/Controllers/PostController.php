<?php

namespace App\Http\Controllers;

use App\Models\GeneratedPost;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController {


    /**
     * @param Post|GeneratedPost $post
     * @param $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPost($post, Request $request)
    {
        $debug = $request->get('debug');
        return view('post', [
            'post' => $post,
            'debug' => $debug
        ]);
    }

}
