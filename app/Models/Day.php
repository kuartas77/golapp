<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed schedules
 */
class Day extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = "days";

    protected $fillable = [
        'days',
        'school_id',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'day_id');
    }

    public function getSchedulAttribute()
    {
        return $this->schedules->pluck('schedule')->implode(', ');
    }
}
