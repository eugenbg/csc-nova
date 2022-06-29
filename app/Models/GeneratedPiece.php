<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

/**
 * @property string content
 * @property string chosen_heading
 * @property mixed original_heading
 * @property mixed id
 * @property mixed heading
 * @property mixed keyword_id
 * @property Keyword keyword
 * @property Piece|null $piece
 * @property array $embedding
 * @property array|null $generated_headings
 * @property int $serp_id
 * @property boolean $chosen
 * @property mixed $piece_id
 * @property string image
 * @property string $formatted_content
 */
class GeneratedPiece extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $casts = [
        'embedding' => 'array',
        'generated_headings' => 'array'
    ];

    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class);
    }

    public function getImage()
    {
        return App::make('url')->to($this->image);
    }

    /**
     * @return BelongsTo
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
