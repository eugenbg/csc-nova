<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ChinaScholarship
 *
 * @property mixed $type
 * @property int $id
 * @property int $university_id
 * @property string $name
 * @property string $link
 * @property string $createdAt
 * @property string $updatedAt
 * @property-read \App\Models\ChinaUniversity $uni
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship whereUniversityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaScholarship whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChinaScholarship extends Model
{
    protected $table = 'uni_scholarships';
    public $timestamps = false;

    /**
     * Get the University that owns this program.
     */
    public function uni(): BelongsTo
    {
        return $this->belongsTo(ChinaUniversity::class, 'university_id');
    }
}
