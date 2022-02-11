<?php

namespace App\Models;

use App\Traits\Fields;
use Illuminate\Support\Str;
use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use SoftDeletes;
    use Fields;
    use GeneralScopes;
    use HasFactory;

    protected $table = "games";

    protected $fillable = [
        'id',
        'tournament_id',
        'competition_group_id',
        'date',
        'hour',
        'num_match',
        'place',
        'rival_name',
        'final_score',
        'general_concept',
        'school_id',
    ];

    protected $appends = [
        'general_concept_short', 'url_destroy', 'url_edit', 'url_update', 'url_show',
    ];

    protected $casts = [
        'final_score' => 'object'
    ];

    public function getGeneralConceptShortAttribute()
    {
        return Str::limit($this->attributes['general_concept'], 100, '...');
    }

    public function getUrlDestroyAttribute()
    {
        return route('matches.destroy', [$this->attributes['id']]);
    }

    public function getUrlEditAttribute()
    {
        return route('matches.edit', [$this->attributes['id']]);
    }

    public function getUrlUpdateAttribute()
    {
        return route('matches.update', [$this->attributes['id']]);
    }

    public function getUrlShowAttribute()
    {
        return route('export.pdf.match', [$this->attributes['id']]);
    }

    public function competitionGroup(): BelongsTo
    {
        return $this->belongsTo(CompetitionGroup::class, 'competition_group_id');
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id');
    }

    public function skillsControls(): HasMany
    {
        return $this->hasMany(SkillsControl::class);
    }
}
