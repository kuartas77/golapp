<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\CompetitionGroupObserver;
use App\Service\Category\CategoryFormatService;
use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $category
 * @property array<int, string>|null $categories
 * @property mixed $year
 * @property mixed $name
 * @property mixed $inscriptions
 *
 * @method onlyTrashedRelations()
 */
class CompetitionGroup extends Model
{
    use GeneralScopes;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'competition_groups';

    protected $fillable = [
        'name',
        'year',
        'tournament_id',
        'user_id',
        'category',
        'categories',
        'school_id',
    ];

    protected $casts = [
        'categories' => 'array',
    ];

    protected $appends = [
        'full_name_group',
        'url_format_match',
    ];

    protected $withCount = [
        'inscriptions',
    ];

    protected static function booted(): void
    {
        self::saving(function (self $group): void {
            $categories = $group->categories;
            $hasExplicitCategories = is_array($categories) && $categories !== [];

            if (! $hasExplicitCategories) {
                $categories = explode(',', (string) $group->category);
            }

            $categories = app(CategoryFormatService::class)->normalizeCategories($categories);
            $group->categories = $categories;
            $group->category = implode(', ', $categories);

            if ($hasExplicitCategories) {
                $group->year = $categories[0] ?? (string) $group->year;
            }
        });

        self::observe(CompetitionGroupObserver::class);
    }

    public function scopeOnlyTrashedRelations($query)
    {
        return $query->with([
            'tournament',
            'professor' => fn ($query) => $query->withTrashed()->get(),
        ])->onlyTrashed();
    }

    public function getFullNameGroupAttribute(): string
    {
        return sprintf('%s (%s)', $this->name, $this->category);
    }

    public function getFullNameAttribute(): string
    {
        return sprintf('%s (%s)', $this->name, $this->category);
    }

    public function getUrlFormatMatchAttribute()
    {
        return route('export.match_detail', [$this->attributes['id']]);
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id')->withTrashed();
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function scopeByInstructor(Builder $query, ?int $userId = null): void
    {
        $query->where('competition_groups.user_id', $userId ?: auth()->id());
    }

    public function matches(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function inscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Inscription::class)->using(CompetitionGroupInscription::class)->where('inscriptions.year', now()->year);
    }
}
