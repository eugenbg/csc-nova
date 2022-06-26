<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property mixed content
 * @property mixed heading
 * @property mixed id
 * @property array embedding
 * @property Keyword keyword
 * @property int serp_id
 * @property mixed chosen
 * @property mixed keyword_id
 * @property GeneratedPiece[]|Collection generatedPieces
 * @property Serp $serp
 */
class Piece extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $casts = [
        'embedding' => 'array'
    ];

    /**
     * @return BelongsTo
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    public function words()
    {
        return count(explode(' ', $this->content));
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
    public function serp(): BelongsTo
    {
        return $this->belongsTo(Serp::class);
    }
}
