<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = "profiles";

    protected $fillable = [
        'id',
        'user_id',
        'date_birth',
        'identification_document',
        'gender',
        'address',
        'phone',
        'mobile',
        'studies',
        'references',
        'contacts',
        'experience',
        'position',
        'aptitude',
        'school_id',
    ];

    protected $appends = ['url_update', 'url_show'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlUpdateAttribute(): string
    {
        return route('profiles.update', [$this->attributes['id']]);
    }

    public function getUrlShowAttribute(): string
    {
        return route('profiles.show', [$this->attributes['id']]);
    }
}
