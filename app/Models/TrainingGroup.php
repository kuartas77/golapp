<?php

namespace App\Models;

use App\Models\User;
use App\Traits\Fields;
use App\Traits\GeneralScopes;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
    use Fields;
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
        'schedule_id',
        'day_id',
        'school_id'
    ];

    protected $appends = [
        'full_schedule_group',
        'full_group',
        'explode_name',
        'url_edit',
        'url_show',
        'url_destroy'
    ];

    public function scopeOnlyTrashedRelations($query)
    {
        return $query->with([
            'schedule.day' => fn ($query) => $query->withTrashed(),
            'professor' => fn ($query) => $query->withTrashed()
        ])->withTrashed();
    }

    public function scopeOnlyTrashedRelationsFilter($query)
    {
        return $query->with([
            'schedule.day' => fn ($query) => $query->withTrashed(),
            'professor' => fn ($query) => $query->withTrashed(),
            'assists' => fn ($query) => $query->select('training_group_id','year')
                ->distinct()
                ->where('year','<', now()->year)
                ->orderBy('year','desc')
                ->withTrashed()
        ])->withTrashed();
    }

    public function scopeOnlyTrashedRelationsPayments($query)
    {
        return $query->with([
            'schedule.day' => fn ($query) => $query->withTrashed(),
            'payments' => fn ($query) => $query->select('training_group_id','year')
                ->distinct()
                ->where('year','<', now()->year)
                ->orderBy('year','desc')
                ->withTrashed()
        ])->withTrashed();
    }

    public function getExplodeNameAttribute(): Collection
    {
        $explode = explode(",", $this->schedule->day->days);
        return collect([
            'days' => $explode,
            'count_days' => count($explode)
        ]);
    }

    public function getFullGroupAttribute(): string
    {
        return $this->nameGroup();
    }

    private function nameGroup($full = false): string
    {
        $var = trim("{$this->name} {$this->stage}");
        $var .= ' (' . trim("{$this->year} {$this->year_two} {$this->year_three} {$this->year_four} {$this->year_five} {$this->year_six} {$this->year_seven} {$this->year_eight} {$this->year_nine} {$this->year_ten} {$this->year_eleven} {$this->year_twelve}") . ') ';
        if ($this->relationLoaded('schedule.day')  && $full) {
            $var .= trim("{$this->schedule->day->days} {$this->schedule->schedule}");
        }
        return $var;
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

    public function setCategoryAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['category'] = implode(',', $value);
        }
    }

    public function getCategoryAttribute()
    {
        return explode(',', $this->attributes['category']);
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

}
