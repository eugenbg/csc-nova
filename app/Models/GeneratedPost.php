<?php

namespace App\Models;

use App\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $keyword_id
 * @property Keyword keyword
 * @property mixed $meta_title
 * @property mixed $title
 * @property mixed $content
 * @property mixed $debug_content
 * @property \Illuminate\Support\Carbon|mixed $published_at
 * @property mixed $slug
 * @property mixed $source_url
 * @property GeneratedPiece[] chosenGeneratedPieces
 * @property mixed $local_category
 */
class GeneratedPost extends Post
{
    use HasFactory;

    public function words()
    {
        $words = 0;
        foreach ($this->chosenGeneratedPieces as $chosenGeneratedPiece) {
            $words += Helper::words($chosenGeneratedPiece->content);
        }

        return $words;
    }

    /**
     * @return BelongsTo
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    /**
     * @return HasMany
     */
    public function chosenGeneratedPieces(): HasMany
    {
        return $this->hasMany(GeneratedPiece::class)
            ->where('chosen', '=', 1);
    }


}
