<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolOutingActivity extends Model
{
    use GeneralScopes;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_outing_id',
        'school_id',
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function outing(): BelongsTo
    {
        return $this->belongsTo(SchoolOuting::class, 'school_outing_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SchoolOutingContribution::class, 'school_outing_activity_id');
    }
}
