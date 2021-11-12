<?php

namespace App\Models;

use App\Traits\Fields;
use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tournament extends Model
{
    use SoftDeletes;
    use Fields;
    use GeneralScopes;
    use HasFactory;

    protected $fillable = [
        'name'
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
}
