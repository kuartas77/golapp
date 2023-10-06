<?php

namespace App\Models;

use App\Models\User;
use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property mixed category
 * @property mixed year
 * @property mixed name
 * @property mixed inscriptions
 * @method onlyTrashedRelations()
 */
class CompetitionGroup extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;

    protected $table = 'competition_groups';
    protected $fillable = [
        'name',
        'year',
        'tournament_id',
        'user_id',
        'category',
        'school_id'
    ];

    protected $casts = [

    ];

    protected $appends = [
        'full_name_group'
    ];

    protected $withCount = [
        'inscriptions'
    ];

    public function scopeOnlyTrashedRelations($query)
    {
        return $query->with([
            'tournament',
            'professor' => fn ($query) => $query->withTrashed()->get()
        ])->onlyTrashed();
    }

    public function getFullNameGroupAttribute()
    {
        return "{$this->name} ({$this->category})";
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->category})";
    }

    /**
     * @return BelongsTo
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     **/
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * @return HasMany
     **/
    public function matches(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /**
     * @return HasMany
     */
    public function inscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Inscription::class)->using(CompetitionGroupInscription::class);
    }
}
