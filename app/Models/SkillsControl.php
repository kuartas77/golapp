<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkillsControl extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;

    public $table = 'skills_control';

    protected $fillable = [
        'game_id',
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
        'school_id',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'inscription_id', 'id');
    }

    public function player(): HasOneThrough
    {
        return $this->hasOneThrough(Player::class, Inscription::class, 'id', 'id', 'inscription_id', 'player_id');
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

}
