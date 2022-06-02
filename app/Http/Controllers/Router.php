<?php

namespace App\Http\Controllers;

use App\Models\ChinaUniversity;
use App\Models\GeneratedPost;
use App\Models\Page;
use App\Models\Post;
use App\Models\Category;
use App\Models\Slug;
use App\Models\SmallPost;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router {

    public function routeMatch($slug, Request $request)
    {
        /** @var Slug $slugModel */
        $slugModel = Slug::query()
            ->where('slug', '=', $slug)
            ->first();

        if(!$slugModel) {
            throw new NotFoundHttpException('Sorry this page does not exist');
        }

        switch ($slugModel->type) {
            case Post::class:
                $controller = resolve(PostController::class);
                $post = Post::query()->find($slugModel->object_id);
                return $controller->getPost($post, $request);
            case GeneratedPost::class:
                $controller = resolve(PostController::class);
                $post = GeneratedPost::query()->find($slugModel->object_id);
                return $controller->getPost($post, $request);
            case Category::class:
                $controller = resolve(CategoryController::class);
                return $controller->getCategory($slugModel->object_id, $request);
            case Page::class:
                $controller = resolve(PageController::class);
                return $controller->page($slugModel->object_id, $request);
        }
    }

}
