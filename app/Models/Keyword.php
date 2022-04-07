<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property Piece[]|Collection pieces
 * @property mixed id
 * @property Serp[]|Collection serps
 * @property GeneratedPiece[] generatedPieces
 */
class Keyword extends Model
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
     * @return HasMany
     */
    public function serps(): HasMany
    {
        return $this->hasMany(Serp::class);
    }

    /**
     * @return HasMany
     */
    public function generatedPieces(): HasMany
    {
        return $this->hasMany(GeneratedPiece::class);
    }

}
