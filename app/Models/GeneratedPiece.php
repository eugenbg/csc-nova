<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string content
 * @property int original_piece_id
 * @property mixed original_heading
 * @property mixed id
 * @property mixed heading
 * @property mixed keyword_id
 * @property Piece|null $piece
 * @property array $embedding
 */
class GeneratedPiece extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $casts = [
        'embedding' => 'array'
    ];

    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class, 'original_piece_id');
    }
}
