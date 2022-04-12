<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

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

    protected $with = [
        'school'
    ];

    public function setPasswordAttribute($value)
    {
        if($value){
            $this->attributes['password'] = Hash::make($value);
        }
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
        return $this->hasOneThrough(School::class, SchoolUser::class, 'user_id','id','id','school_id');
    }


}
