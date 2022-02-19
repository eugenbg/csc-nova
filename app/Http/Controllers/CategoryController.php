<?php


namespace App\Http\Controllers;


use App\Models\Category;

class CategoryController
{
    /**
     * @param int $categoryId
     * @param $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCategory($categoryId, $request)
    {
        $category = Category::query()->find($categoryId);
        return view('category', ['category' => $category]);
    }

}
