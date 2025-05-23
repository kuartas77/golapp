<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @method static select(string $string)
 * @method static updateOrCreate(array $array, array $array1)
 */
class Assist extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;

    protected $table = 'assists';

    protected $fillable = [
        'training_group_id',
        'inscription_id',
        'year',
        'month',
        'assistance_one',
        'assistance_two',
        'assistance_three',
        'assistance_four',
        'assistance_five',
        'assistance_six',
        'assistance_seven',
        'assistance_eight',
        'assistance_nine',
        'assistance_ten',
        'assistance_eleven',
        'assistance_twelve',
        'assistance_thirteen',
        'assistance_fourteen',
        'assistance_fifteen',
        'assistance_sixteen',
        'assistance_seventeen',
        'assistance_eighteen',
        'assistance_nineteen',
        'assistance_twenty',
        'assistance_twenty_one',
        'assistance_twenty_two',
        'assistance_twenty_three',
        'assistance_twenty_four',
        'assistance_twenty_five',
        'observations',
        'school_id',
    ];

    protected $appends = [];

    protected $casts = [
        'observations' => 'object'
    ];

    public function scopeOnlyTrashedRelations($query)
    {
        return $query->with([
            'inscription' => fn($query) => $query->withTrashed()
        ])->withTrashed();
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     */
    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id', 'id')->withTrashed();
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     */
    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'inscription_id', 'id')->withTrashed();
    }

    public function player(): HasOneThrough
    {
        return $this->hasOneThrough(Player::class, Inscription::class, 'id', 'id', 'inscription_id', 'player_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function getUrlHistoricAttribute()
    {
        return route('historic.assists.group', [
            'training_group_id' => $this->attributes['training_group_id'],
            'year' => $this->attributes['year']
        ]);
    }

    public function getMonthsAttribute()
    {
        return DB::table('assists')->selectRaw('distinct month')
            ->where('year', $this->attributes['year'])->distinct()
            ->where('training_group_id', $this->attributes['training_group_id'])
            ->get()->implode('month', ', ');
    }

    public function getMonthAttribute():string
    {
        $value = $this->attributes['month'];
        $months = config('variables.KEY_MONTHS_INDEX');
        $months[] = config('variables.KEY_MONTHS');
        return $months[$value];
    }
}
