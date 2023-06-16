<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TournamentPayout extends Model
{
    use HasFactory;
    use GeneralScopes;

    protected $table = "tournament_payouts";

    protected $fillable = [
        'school_id',
        'inscription_id',
        'tournament_id',
        'competition_group_id',
        'year',
        'unique_code',
        'status',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function competitionGroup(): BelongsTo
    {
        return $this->belongsTo(CompetitionGroup::class, 'competition_group_id');
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
