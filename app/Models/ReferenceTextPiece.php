<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property $embedding
 * @property $text
 */
class ReferenceTextPiece extends Model
{
    use HasFactory;

    public $casts = [
        'embedding' => 'array'
    ];

    /**
     * @return BelongsTo
     */
    public function reference_text(): BelongsTo
    {
        return $this->belongsTo(ReferenceText::class);
    }

}
