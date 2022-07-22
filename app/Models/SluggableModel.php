<?php

namespace App\Models;

use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
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

        $this->slug = $this->getFreeSlug($this->slug);
        $slugModel = new Slug();
        $slugModel->type = get_class($this);
        $slugModel->object_id = $this->id;
        $slugModel->slug = $this->slug;
        $slugModel->save();

        return parent::save($options);
    }

    public function cleanSlugs(string $title)
    {
        \App\Models\Slug::query()
            ->where('slug', '=', Str::slug($title))
            ->delete();
    }

    private function getFreeSlug(string $slug)
    {
        $exists = \App\Models\Slug::query()->where('slug', '=', $slug)->exists();
        if($exists) {
            return $slug . '-' . Uuid::uuid4();
        }

        return $slug;
    }
}
