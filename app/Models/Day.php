<?php

namespace App\Models;

use App\Traits\Fields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property mixed schedules
 */
class Day extends Model
{
    use SoftDeletes;
    use Fields;
    use HasFactory;

    protected $table = "days";

    protected $fillable = [
        'days'
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
