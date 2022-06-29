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
        $content = preg_replace(
            '/20\d{2}.20\d{2}/',
            sprintf('%s-%s', now()->year, now()->addYear()->year),
            $generatedPiece->content
        );

        $content = preg_replace(
            sprintf('/(?!%s)20\d{2}/', now()->addYear()->year),
            now()->year,
            $content
        );

        $content = str_replace('. .', '.', $content);

        $content = self::formatHeadings($content);
        $content = self::formatParagraphs($content);
        $generatedPiece->formatted_content = $content;
        $generatedPiece->save();

        $generatedPiece = self::addWebsiteLinks($generatedPiece);


        return $generatedPiece->formatted_content;
    }

    /**
     * if there are two line breaks after a phrase which does not end with : sign, make it a heading
     * @param string $content
     * @return string
     */
    private static function formatHeadings(string $content): string
    {
        $matches = [];
        preg_match_all('/((^|\n)(?<heading>( |\w+){1,13})\n\n).*/', $content, $matches);
        if(isset($matches['heading']) && count($matches['heading'])) {
            foreach ($matches['heading'] as $heading) {
                $content = str_replace($heading, "<h4>{$heading}</h4>", $content);
            }
        }

        return $content;
    }

    private static function formatParagraphs(string $content): string
    {
        $lines = explode("\n", $content);

        $newLines = [];
        foreach ($lines as $line) {
            if(strpos($line, '<h4>') === false && strlen($line) > 1) {
                $newLines[] = "<p>${line}</p>";
            } elseif (strpos($line, '<h4>') !== false) {
                $newLines[] = $line;
            }
        }

        return implode('', $newLines);
    }

    private static function addWebsiteLinks(GeneratedPiece $generatedPiece): GeneratedPiece
    {
        if(strpos($generatedPiece->formatted_content, '>website<') !== false) {
            return $generatedPiece;
        }

        $website = $generatedPiece->keyword->additional_data['website'] ?? null;
        if($website) {
            $generatedPiece->formatted_content = str_replace(
                'website',
                sprintf('<a href="%s" rel="nofollow" target="_blank">website</a>', $website),
                $generatedPiece->formatted_content
            );

            $generatedPiece->save();
        }

        return $generatedPiece;
    }

}
