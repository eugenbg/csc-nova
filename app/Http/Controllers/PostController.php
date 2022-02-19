<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController {


    /**
     * @param int $postId
     * @param $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPost($postId, $request)
    {
        $post = Post::query()->find($postId);
        return view('post', ['post' => $post]);
    }

}
