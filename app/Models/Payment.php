<?php

namespace App\Models;

use App\Traits\Fields;
use App\Traits\PaymentTrait;
use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Payment extends Model
{
    use SoftDeletes;
    use Fields;
    use GeneralScopes;
    use HasFactory;
    use PaymentTrait;

    protected $table = "payments";
    protected $fillable = [
        'year',
        'training_group_id',
        'inscription_id',
        'unique_code',
        'enrollment',
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
        'school_id',
        'deleted_at'
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'inscription_id', 'id');
    }

    public function player(): HasOneThrough
    {
        return $this->hasOneThrough(Player::class, Inscription::class, 'id','id','inscription_id', 'player_id');
    }

    public function training_group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id', 'id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function getUrlHistoricAttribute()
    {
        return route('historic.payments.group', [
            'training_group_id' => $this->attributes['training_group_id'],
            'year' => $this->attributes['year']
        ]);
    }
}
