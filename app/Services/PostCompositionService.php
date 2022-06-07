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

        $content = '';
        $debugContent = '';
        foreach ($generatedPieces as $generatedPiece) {
            $debugContent .= sprintf('<h2>ORIGINAL: %s</h2>', $generatedPiece->heading);
            $debugContent .= sprintf('<h2>GENERATED: %s</h2>', $generatedPiece->chosen_heading);
            $content .= sprintf('<h2>%s</h2>', $generatedPiece->chosen_heading);
            $debugContent .= sprintf(
                '<p>Original content: %s</p><p>Generated content: %s</p>',
                $generatedPiece->piece->content,
                $generatedPiece->content
            );

            $content .= sprintf('<p>%s</p>', $generatedPiece->content);
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
        $post->debug_content = $debugContent;
        $post->content = $content;
        $post->published_at = now();
        $post->save();

        //assign pieces to the new post
        GeneratedPiece::query()
            ->whereIn('id', $generatedPieces->pluck('id')->toArray())
            ->update(['generated_post_id' => $post->id]);

        return $post;
    }

}
