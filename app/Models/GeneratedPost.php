<?php

namespace App\Models;

use App\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $keyword_id
 * @property mixed $meta_title
 * @property mixed $title
 * @property mixed $content
 * @property mixed $debug_content
 * @property \Illuminate\Support\Carbon|mixed $published_at
 * @property mixed $slug
 */
class GeneratedPost extends Post
{
    use HasFactory;

    public function words()
    {
        return Helper::words($this->content);
    }
}
