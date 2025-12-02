<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use App\Traits\PaymentTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
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
        'enrollment_amount',
        'january_amount',
        'february_amount',
        'march_amount',
        'april_amount',
        'may_amount',
        'june_amount',
        'july_amount',
        'august_amount',
        'september_amount',
        'october_amount',
        'november_amount',
        'december_amount',
        'school_id',
        'deleted_at'
    ];

    static $pending = 0;
    static $paid = 1;
    static $debt = 2;
    static $paid_ = 3;
    static $disability = 4;
    static $temporary_retirement = 5;
    static $permanent_retirement = 6;
    static $other = 7;
    static $scholarship_recipient = 8;
    static $paid_cash = 9;
    static $paid_deposit = 10;
    static $annuity_payment_deposit = 11;
    static $annuity_payment_cash = 12;
    static $payment_agreement = 13;
    static $no_application = 14;

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'inscription_id', 'id');
    }

    public function player(): HasOneThrough
    {
        return $this->hasOneThrough(Player::class, Inscription::class, 'id', 'id', 'inscription_id', 'player_id');
    }

    public function training_group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class, 'training_group_id', 'id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function getUrlHistoricAttribute(): string
    {
        return route('historic.payments.group', [
            'training_group_id' => $this->attributes['training_group_id'],
            'year' => $this->attributes['year']
        ]);
    }
}
