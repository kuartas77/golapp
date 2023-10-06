<?php

namespace App\Models;

use App\Models\User;
use App\Observers\SchoolObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property string name
 * @property string agent
 * @property string address
 * @property string phone
 * @property string email
 * @property bool is_enable
 * @property string logo
 */
class School extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const KEY_SCHOOL_CACHE = 'school_';

    protected $table = "schools";
    protected $fillable = [
        'name',
        'agent',
        'address',
        'phone',
        'email',
        'is_enable',
        'logo',
        'slug'
    ];

    protected $casts = [
        'is_enable' => 'boolean'
    ];

    protected $appends = [
        'logo_file',
        'settings'
    ];

    protected static function booted()
    {
        self::observe(SchoolObserver::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setLogoAttribute($value)
    {
        if (!empty($value)) {
            if(!empty($this->attributes['logo'])){
                Storage::disk('public')->delete($this->attributes['logo']);
            }

            $this->attributes['logo'] = $value;
        }
    }

    public function getLogoFileAttribute(): string
    {
        if (!empty($this->attributes['logo']) && Storage::disk('public')->exists($this->attributes['logo'])) {
            return route('images', $this->attributes['logo']);
        }
        return asset('img/ballon.png');
    }

    public function getUrlEditAttribute(): string
    {
        return route('config.schools.edit', [$this->attributes['slug']]);
    }

    public function getUrlUpdateAttribute(): string
    {
        return route('config.schools.update', [$this->attributes['slug']]);
    }

    public function getUrlShowAttribute(): string
    {
        return route('config.schools.show', [$this->attributes['slug']]);
    }

    public function getUrlDestroyAttribute(): string
    {
        return route('config.schools.destroy', [$this->attributes['slug']]);
    }

    public function getLogoLocalAttribute(): string
    {
        if (!empty($this->attributes['logo']) && Storage::disk('public')->exists($this->attributes['logo'])) {
            return storage_path("app/public/{$this->attributes['logo']}");
        }
        return storage_path('standard/ballon.png');
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, SchoolUser::class, 'school_id','id','id','user_id');
    }

    public function admin(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, SchoolUser::class, 'school_id','id','id','user_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tournament_payouts(): HasMany
    {
        return $this->hasMany(TournamentPayout::class);
    }

    public function assists(): HasMany
    {
        return $this->hasMany(Assist::class);
    }

    public function skillControls(): HasMany
    {
        return $this->hasMany(SkillsControl::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function trainingGroups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }

    public function competitionGroups(): HasMany
    {
        return $this->hasMany(CompetitionGroup::class);
    }

    public function settingsValues(): HasMany
    {
        return $this->hasMany(SettingValue::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function configDefault()
    {
        $this->schedules()->create([
            'schedule' => '10:00AM - 11:00AM',
        ]);

        $this->trainingGroups()->create([
            'name' => 'Provisional',
            'year' => now()->year,
            'category' => 'Todas las categorías',
            'days' => 'Grupo predeterminado',
            'schedules' => '10:00AM - 11:00AM',
        ]);

        $this->settingsValues()->createMany(SettingValue::settingsDefault($this->id));
    }

    public function getSettingsAttribute()
    {
        if($this->relationLoaded('settingsValues')){
            return $this->settingsValues->mapWithKeys(function ($setting) {
                return [ $setting->setting_key => $setting->value ];
            });
        }
    }

}
