<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tournament extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;

    protected $fillable = [
        'name','school_id',
    ];

    protected $appends = [
        'url_destroy'
    ];

    public function getUrlDestroyAttribute(): string
    {
        return route('tournaments.destroy', [$this->attributes['id']]);
    }

    public function competitionGroup(): HasMany
    {
        return $this->hasMany(CompetitionGroup::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
