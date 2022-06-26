<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property Piece[]|Collection pieces
 * @property int id
 * @property array embedding
 * @property Serp[]|Collection serps
 * @property Collection|GeneratedPiece[] generatedPieces
 * @property string $keyword
 * @property HasMany $chosenGeneratedPieces
 * @property string keyword_frase
 * @property array additional_data
 * @property GeneratedPost generatedPost
 * @property string object_name
 */
class Keyword extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    public $casts = [
        'embedding' => 'array',
        'additional_data' => 'array'
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
    public function serps(): HasMany
    {
        return $this->hasMany(Serp::class);
    }

    public function chosenSerp()
    {
        return $this->serps->first(function(Serp $serp) {
            return $serp->chosen;
        });
    }

    /**
     * @return HasMany
     */
    public function generatedPieces(): HasMany
    {
        return $this->hasMany(GeneratedPiece::class);
    }

    /**
     * @return HasMany
     */
    public function chosenGeneratedPieces(): HasMany
    {
        return $this->hasMany(GeneratedPiece::class)
            ->where('chosen', '=', 1);
    }

    public function generatedPost(): HasOne
    {
        return $this->hasOne(GeneratedPost::class);
    }

}
