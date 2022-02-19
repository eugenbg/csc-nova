<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ChinaUniProgram
 *
 * @property mixed $type
 * @property int $id
 * @property int $university_id
 * @property string $url
 * @property string|null $local_path
 * @property string $createdAt
 * @property string $updatedAt
 * @property-read \App\Models\ChinaUniversity $uni
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage whereLocalPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage whereUniversityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniImage whereUrl($value)
 * @mixin \Eloquent
 */
class ChinaUniImage extends Model
{
    protected $table = 'uni_images';
    public $timestamps = false;

    const TYPE_CAMPUS = 'campus';
    const TYPE_DORM = 'dorm';

    /**
     * Get the University that owns this program.
     */
    public function uni(): BelongsTo
    {
        return $this->belongsTo(ChinaUniversity::class, 'university_id');
    }
}
