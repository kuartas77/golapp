<?php /** @noinspection PhpUndefinedFieldInspection */

namespace App\Models;

use App\Traits\GeneralScopes;
use App\Traits\CustomModelLogic;
use Illuminate\Support\Collection;
use App\Observers\InscriptionObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property mixed player_id
 * @property mixed unique_code
 * @property mixed id
 * @property mixed training_group_id
 * @property Collection|mixed average
 * @property mixed competition_group_id
 * @property mixed|string start_date
 * @property mixed copy_identification_document
 * @property mixed eps_certificate
 * @property mixed medic_certificate
 * @property mixed study_certificate
 * @property mixed overalls
 * @property mixed ball
 * @property mixed bag
 * @property mixed presentation_uniform
 * @property mixed tournament_pay
 * @property mixed competition_uniform
 * @property mixed photos
 * @property mixed category
 * @property mixed period_one
 * @property mixed period_two
 * @property mixed period_three
 * @property mixed period_four
 */
class Inscription extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;
    use CustomModelLogic;

    protected $table = "inscriptions";

    protected $fillable = [
        'id',
        'player_id',
        'unique_code',
        'year',
        'training_group_id',
        'competition_group_id',
        'start_date',
        'category',
        'photos',
        'scholarship',
        'copy_identification_document',
        'eps_certificate',
        'medic_certificate',
        'study_certificate',
        'overalls',
        'ball',
        'bag',
        'presentation_uniform',
        'competition_uniform',
        'tournament_pay',
        'period_one',
        'period_two',
        'period_three',
        'period_four',
        'school_id',
        'deleted_at',
        'pre_inscription'
    ];

    protected $casts = [
        'start_date' => "datetime:Y-m-d",
        'created_at' => "datetime:Y-m-d",
    ];

    public static $documentFields = [
        'player_document',
        'medical_certificate',
        'tutor_document',
        'payment_receipt'
    ];

    protected $appends = ['url_edit','url_update','url_show', 'url_impression', 'url_destroy'];

    protected static function booted()
    {
        self::observe(InscriptionObserver::class);
    }

    public function scopeWhereCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeWhereCompetition($query, $competitionGroup = null)
    {
        return $query->where('competition_group_id', $competitionGroup);
    }

    public function scopeWithTrashedRelations($query)
    {
        return $query->withTrashed()->with([
            'payments' => fn($query) => $query->withTrashed(),
            'assistance' => fn($query) => $query->orderByRaw("MONTH(CONCAT('2000-', assists.month, '-01')) asc")->withTrashed(),
            'skillsControls' => fn($query) => $query->withTrashed()
        ]);
    }

    public function getUrlImpressionAttribute(): string
    {
        return route('export.inscription', [$this->attributes['player_id'], $this->attributes['id']]);
    }

    public function getUrlEditAttribute(): string
    {
        return route('inscriptions.edit', [$this->attributes['id']]);
    }

    public function getUrlUpdateAttribute(): string
    {
        return route('inscriptions.update', [$this->attributes['id']]);
    }

    public function getUrlShowAttribute(): string
    {
        return route('players.show', [$this->attributes['unique_code']]);
    }

    public function getUrlDestroyAttribute(): string
    {
        return route('inscriptions.destroy', [$this->attributes['id']]);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function competitionGroup(): BelongsToMany
    {
        return $this->belongsToMany(CompetitionGroup::class)->using(CompetitionGroupInscription::class);
    }

    public function skillsControls(): HasMany
    {
        return $this->hasMany(SkillsControl::class);
    }

    public function assistance(): HasMany
    {
        return $this->hasMany(Assist::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function tournament_payouts()
    {
        return $this->hasMany(TournamentPayout::class);
    }

    public function getFormatAverageAttribute(): array
    {
        return [
            'total_matches' => $this->skillsControls->count(),
            'assistance' => $this->skillsControls->sum('assistance'),
            'titular' => $this->skillsControls->sum('titular'),
            'played_approx' => $this->skillsControls->sum('played_approx'),
            'played_approx_avg' => round($this->skillsControls->avg('played_approx'), 1),
            'goals' => $this->skillsControls->sum('goals'),
            'goals_avg' => round($this->skillsControls->avg('goals'), 1),
            'red_cards' => $this->skillsControls->sum('red_cards'),
            'red_cards_avg' => round($this->skillsControls->avg('red_cards'), 1),
            'yellow_cards' => $this->skillsControls->sum('yellow_cards'),
            'yellow_cards_avg' => round($this->skillsControls->avg('yellow_cards'), 1),
            'qualification' => round($this->skillsControls->avg('qualification'), 1),
            'positions' => $this->skillsControls->pluck('position')->unique()->implode(','),
        ];
    }

    public function scopeCodeYear($query, string $code, int $year)
    {
        return $query->where('unique_code', $code)->where('year', $year);
    }

    public function scopeYear($query, ?int $year = null)
    {
        $query->where('year', ($year ?: now()->year) );
    }
}
