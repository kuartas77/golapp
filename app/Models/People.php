<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\GuardianPasswordResetNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;


/**
 * @property mixed id
 */
class People extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    const TUTOR = 1;

    const FATHER = 2;

    const MOTHER = 3;

    const FAMILY_ONE = 4;

    const FAMILY_TWO = 5;

    protected $table = 'peoples';

    protected $fillable = [
        'tutor',
        'relationship',
        'names',
        'phone',
        'mobile',
        'identification_card',
        'neighborhood',
        'email',
        'profession',
        'business',
        'position',
        'password',
        'email_verified_at',
        'invited_at',
        'last_login_at',
    ];

    protected $casts = [
        'tutor' => 'integer',
        'email_verified_at' => 'datetime',
        'invited_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['relationship_name'];

    public function setNamesAttribute($value): void
    {
        $this->attributes['names'] = strtoupper($value);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = filled($value)
            ? mb_strtolower(trim((string) $value))
            : null;
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

    public function getRelationshipNameattribute()
    {
        $relationship = config('variables.KEY_RELATIONSHIPS_SELECT');
        return array_key_exists((integer)$this->relationship, $relationship) ? $relationship[(integer)$this->relationship] : '';
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'peoples_players');
    }

    public function routeNotificationForMail($notification): array
    {
        return [$this->email => $this->names];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new GuardianPasswordResetNotification($this, $token));
    }
}
