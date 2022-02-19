<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ChinaUniDorm
 *
 * @property mixed $type
 * @property int $id
 * @property int $university_id
 * @property string|null $rate
 * @property string|null $toilet
 * @property string|null $bathroom
 * @property string|null $internet
 * @property string|null $landline
 * @property string|null $airConditioner
 * @property string|null $comments
 * @property string $createdAt
 * @property string $updatedAt
 * @property-read \App\Models\ChinaUniversity $uni
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereAirConditioner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereBathroom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereInternet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereLandline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereToilet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereUniversityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChinaUniDorm whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChinaUniDorm extends Model
{
    protected $table = 'uni_dorms';
    public $timestamps = false;

    /**
     * Get the University that owns this program.
     */
    public function uni(): BelongsTo
    {
        return $this->belongsTo(ChinaUniversity::class, 'university_id');
    }
}
