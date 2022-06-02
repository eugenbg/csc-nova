<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property Collection|Piece[] pieces
 * @property int da
 * @property int links
 * @property Keyword keyword
 * @property mixed keyword_id
 * @property mixed $url
 * @property mixed $title
 * @property array $title_embedding
 * @property array|mixed $scores
 * @property bool|mixed $chosen
 * @property GeneratedPiece[]|Collection $generatedPieces
 */
class Serp extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    public $score = null;

    public $casts = [
        'title_embedding' => 'array',
        'scores' => 'array'
    ];

    /**
     * @return HasMany
     */
    public function pieces(): HasMany
    {
        return $this->hasMany(Piece::class);
    }

    /**
     * @return HasMany
     */
    public function generatedPieces(): HasMany
    {
        return $this->hasMany(GeneratedPiece::class);
    }

    /**
     * @return BelongsTo
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

}
