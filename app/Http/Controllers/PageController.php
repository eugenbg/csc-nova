<?php


namespace App\Http\Controllers;


use App\Models\Page;
use App\Models\Post;
use App\Models\SmallPost;
use Illuminate\Http\Request;

class PageController
{
    /**
     * @param $pageId
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function page($pageId, Request $request)
    {
        $page = Page::find($pageId);
        return view('page', ['page' => $page]);
    }

}
