<?php

namespace App\Services;

use App\Models\Category;
use App\Models\GeneratedPiece;
use App\Models\GeneratedPost;
use App\Models\Keyword;
use App\Models\Piece;
use App\Models\Serp;
use App\Models\Spell;
use Illuminate\Support\Collection;

class PostCompositionService {

    public static function saveGeneratedPost(Keyword $keyword, Category $category = null): GeneratedPost
    {
        $generatedPieces = $keyword->generatedPieces->filter(function (GeneratedPiece $piece) {
            return $piece->chosen;
        });

        foreach ($generatedPieces as $generatedPiece) {
            self::handlePieceContent($generatedPiece);
        }

        $post = GeneratedPost::query()
            ->where('keyword_id', '=', $keyword->id)
            ->first();

        if(!$post) {
            $post = new GeneratedPost();
        }

        $post->keyword_id = $keyword->id;
        $post->category_id = $post->category_id ?: ($category ? $category->id : null);
        $post->meta_title = $keyword->keyword;
        $post->title = $keyword->keyword;
        $post->published_at = now();
        $post->save();

        //assign pieces to the new post
        GeneratedPiece::query()
            ->whereIn('id', $generatedPieces->pluck('id')->toArray())
            ->update(['generated_post_id' => $post->id]);

        return $post;
    }


    public static function handlePieceContent(GeneratedPiece $generatedPiece)
    {
        $generatedPiece->content = preg_replace(
            '/20\d{2}.20\d{2}/',
            sprintf('%s-%s', now()->year, now()->addYear()->year),
            $generatedPiece->content
        );

        $generatedPiece->content = preg_replace(
            sprintf('/(?!%s)20\d{2}/', now()->addYear()->year),
            now()->year,
            $generatedPiece->content
        );

        $generatedPiece->save();
        return $generatedPiece;
    }

}
