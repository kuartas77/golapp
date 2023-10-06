<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property mixed incidence
 * @property mixed description
 */
class Incident extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'incidents';

    protected $fillable = [
        'user_created_id',
        'user_incident_id',
        'incidence',
        'description',
        'slug_name',
        'school_id',
    ];

    protected $appends = [
        'url_show', 'url_print','incidence_upper', 'description_upper'
    ];

    public function getIncidenceUpperAttribute($value): string
    {
        return Str::upper($this->incidence);
    }

    public function getDescriptionUpperAttribute($value): string
    {
        return Str::upper(wordwrap($this->description, 120, '<br>'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug_name';
    }

    public function professor(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_incident_id')->withTrashed();
    }

    public function user_created(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_created_id');
    }

    public function getUrlShowAttribute(): string
    {
        return route('incidents.show', [$this->attributes['slug_name']]);
    }

    public function getUrlPrintAttribute(): string
    {
        return route('export.pdf.incidents', [$this->attributes['slug_name']]);
    }
}
