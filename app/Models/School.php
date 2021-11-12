<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property string name
 * @property string agent
 * @property string address
 * @property string phone
 * @property string email
 * @property bool is_enable
 * @property string logo
 * @property string logo_min
 */
class School extends Model
{
    use HasFactory;
    
    protected $table = "schools";
    protected $fillable = [
        'name',
        'agent',
        'address',
        'phone',
        'email',
        'is_enable',
        'logo',
        'logo_min',
    ];

    protected $casts = [
        'is_enable' => 'boolean'
    ];

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, 'schools_users', 'school_id', 'user_id');
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

}
