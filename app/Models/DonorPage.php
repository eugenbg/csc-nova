<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $content
 * @property array|mixed $content_pieces
 * @property array|mixed $generated
 * @property mixed|string $category
 * @property array|mixed $categories
 * @property mixed $keywords
 * @property mixed $local_category
 * @property mixed $url
 */
class DonorPage extends Model
{
    use HasFactory;

    protected $casts = [
        'content_pieces' => 'array',
        'keywords' => 'array',
        'donor_categories' => 'array',
    ];

    public $timestamps = false;
}
