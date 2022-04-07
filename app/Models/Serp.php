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
 */
class Serp extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    /**
     * @return HasMany
     */
    public function pieces(): HasMany
    {
        return $this->hasMany(Piece::class);
    }

    /**
     * @return BelongsTo
     */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

}
