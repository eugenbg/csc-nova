<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function generatedPieces()
    {
        return $this->hasMany(GeneratedPiece::class, 'original_piece_id');
    }
}
