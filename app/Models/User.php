<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\UserPasswordResetNotification;
use App\Service\Auth\AuthUserContext;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    public const SUPER_ADMIN = 1;

    public const SCHOOL = 2;

    public const INSTRUCTOR = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'school_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'url_activate'
    ];

    protected static function booted(): void
    {
        static::saved(fn (self $user) => AuthUserContext::forgetUser($user->id));
        static::deleted(fn (self $user) => AuthUserContext::forgetUser($user->id));
        static::restored(fn (self $user) => AuthUserContext::forgetUser($user->id));
    }

    public function setPasswordAttribute($value): void
    {
        if (blank($value)) {
            return;
        }

        $this->attributes['password'] = Hash::needsRehash((string) $value)
            ? Hash::make((string) $value)
            : (string) $value;
    }

    public function competition_groups(): HasMany
    {
        return $this->hasMany(CompetitionGroup::class);
    }

    public function training_groups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function getUrlActivateAttribute()
    {
        return route('users.activate', [$this->attributes['id']]);
    }

    public function school(): HasOneThrough
    {
        return $this->hasOneThrough(School::class, SchoolUser::class, 'user_id', 'id', 'id', 'school_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(TrainingGroup::class)->withPivot('assigned_year');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new UserPasswordResetNotification($this, $token));
    }
}
