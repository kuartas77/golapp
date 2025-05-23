<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Observers\TrainingGroupObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static onlyTrashedRelations()
 * @property mixed year_five
 * @property mixed year_four
 * @property mixed year
 * @property mixed year_two
 * @property mixed year_six
 * @property mixed year_three
 * @property mixed year_seven
 * @property mixed year_eight
 * @property mixed year_nine
 * @property mixed year_ten
 * @property mixed year_eleven
 * @property mixed year_twelve
 * @property mixed inscriptions
 * @property mixed schedule
 * @property mixed stage
 * @property mixed name
 * @property mixed created_at
 */
class TrainingGroup extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;

    protected $table = 'training_groups';

    protected $fillable = [
        'user_id',
        'name',
        'stage',
        'year',
        'year_two',
        'year_three',
        'year_four',
        'year_five',
        'year_six',
        'year_seven',
        'year_eight',
        'year_nine',
        'year_ten',
        'year_eleven',
        'year_twelve',
        'category',
        'schedules',
        'days',
        'school_id',
        'year_active'
    ];

    protected $appends = [
        'full_schedule_group',
        'full_group',
        'explode_name',
        'explode_days',
        'explode_schedules',
        'url_edit',
        'url_show',
        'url_destroy',
        'instructors_names',
        'instructors_ids'
    ];

    protected static function booted(): void
    {
        self::observe(TrainingGroupObserver::class);
    }

    public function scopeOnlyTrashedRelations($query)
    {
        return $query->with([
            'instructors' => fn($query) => $query->withTrashed()
        ])->withTrashed();
    }

    public function scopeOnlyTrashedRelationsFilter($query)
    {
        return $query->with([
            'instructors' => fn($query) => $query->withTrashed(),
            'assists' => fn($query) => $query->select('training_group_id', 'year')
                ->distinct()
                ->where('year', '<', now()->year)
                ->orderBy('year', 'desc')
                ->withTrashed()
        ])->withTrashed();
    }

    public function scopeOnlyTrashedRelationsPayments($query)
    {
        return $query->with([
            'payments' => fn($query) => $query->select('training_group_id', 'year')
                ->distinct()
                ->where('year', '<', now()->year)
                ->orderBy('year', 'desc')
                ->withTrashed()
        ])->withTrashed();
    }

    public function getExplodeNameAttribute(): Collection
    {
        if(property_exists($this, 'days') && $this->days !== null) {

            $explode = explode(",", $this->days);
            return collect([
                'days' => $explode,
                'count_days' => count($explode)
            ]);
        }

        return collect();
    }

    public function getFullGroupAttribute(): string
    {
        return $this->nameGroup();
    }

    private function nameGroup(bool $full = false): string
    {
        if ($this->name !== 'Provisional') {
            $optional = ($this->year_active ?? $this->days);
            $var = sprintf('%s => %s %s', $optional, $this->name, $this->stage);
            $years = array_filter([$this->year, $this->year_two, $this->year_three, $this->year_four, $this->year_five, $this->year_six, $this->year_seven, $this->year_eight, $this->year_nine, $this->year_ten, $this->year_eleven, $this->year_twelve]);
            $var .= sprintf(' (%s) ', implode(',', $years));

            if ($full) {
                $var .= sprintf('%s %s', $this->days, $this->schedules);
            }
        }else{
            $var = sprintf('%s (%s)', $this->name, implode(',', $this->category));
        }
        return trim($var);


    }

    public function getFullScheduleGroupAttribute(): string
    {
        return $this->nameGroup(true);
    }

    public function getUrlEditAttribute(): string
    {
        return route('training_groups.edit', [$this->attributes['id']]);
    }

    public function getUrlShowAttribute(): string
    {
        return route('training_groups.show', [$this->attributes['id']]);
    }

    public function getUrlDestroyAttribute(): string
    {
        return route('training_groups.destroy', [$this->attributes['id']]);
    }

    public function setCategoryAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['category'] = implode(',', $value);
        }
    }

    public function setSchedulesAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['schedules'] = implode(',', $value);
        }
    }

    public function setDaysAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['days'] = implode(',', $value);
        }
    }

    public function getCategoryAttribute()
    {
        return explode(',', ($this->attributes['category'] ?? ''));
    }

    public function getExplodeDaysAttribute()
    {
        return explode(',', ($this->attributes['days'] ?? ''));
    }

    public function getExplodeSchedulesAttribute()
    {
        return explode(',', ($this->attributes['schedules'] ?? ''));
    }

    public function getInstructorsNamesAttribute()
    {
        if ($this->relationLoaded('instructors')) {
            return $this->instructors->implode('name', ', ');
        }
        return '';
    }

    public function getInstructorsIdsAttribute()
    {
        if ($this->relationLoaded('instructors')) {
            return $this->instructors->pluck('id');
        }
        return [];
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'id');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'training_group_id');
    }

    public function assists(): HasMany
    {
        return $this->hasMany(Assist::class, 'training_group_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'training_group_id');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('assigned_year');
    }

    public function ScopeByInstructor(Builder $builder, $year = null): void
    {
        $builder->whereRelation('instructors', 'training_group_user.user_id', auth()->id())
            ->whereRelation('instructors', 'assigned_year', $year ?: now()->year);
    }

    public function members()
    {
        return $this->hasManyThrough(Player::class, Inscription::class, 'training_group_id', 'id', 'id', 'player_id')->where('inscriptions.year', now()->year);
    }

}
