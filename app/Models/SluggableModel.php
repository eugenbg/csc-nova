<?php

namespace App\Models;

use App\Models\Slug;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * App\Models\SluggableModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SluggableModel findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SluggableModel whereSlug($slug)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SluggableModel query()
 * @property string $slug
 * @property string $title
 * @property int $id
 */
class SluggableModel extends Model
{

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if(!$this->slug) {
            $this->slug = Str::slug($this->title);
        }

        parent::save($options);

        $slugModel = Slug::where('slug', '=', $this->slug)->first();
        if(!$slugModel) {
            $slugModel = new Slug();
            $slugModel->type = get_class($this);
            $slugModel->object_id = $this->id;
            $slugModel->slug = $this->slug;
            $slugModel->save();
        }

        if($slugModel
            && ($slugModel->object_id != $this->id || $slugModel->type != get_class($this))
        ) {
            throw new BadRequestException('This slug is already taken');
        }

        return parent::save($options);
    }
}
