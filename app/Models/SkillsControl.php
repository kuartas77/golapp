<?php

namespace App\Models;

use App\Traits\Fields;
use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class SkillsControl extends Model
{
    use SoftDeletes;
    use Fields;
    use GeneralScopes;
    use HasFactory;

    public $table = 'skills_control';

    protected $fillable = [
        'match_id',
        'inscription_id',
        'assistance',
        'titular',
        'played_approx',
        'position',
        'goals',
        'red_cards',
        'yellow_cards',
        'qualification',
        'observation',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'inscription_id', 'id');
    }

    public function player(): HasOneThrough
    {
        return $this->hasOneThrough(Player::class, Inscription::class, 'id','id','inscription_id', 'player_id');
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'match_id', 'id');
    }

}
