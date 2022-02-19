<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChinaUniLink extends Model
{
    use HasFactory;

    protected $table = 'china_universities_interlinking';

    /**
     * Get the University that owns this program.
     */
    public function linkedUni(): BelongsTo
    {
        return $this->belongsTo(ChinaUniversity::class, 'linked_uni_id');
    }
}
