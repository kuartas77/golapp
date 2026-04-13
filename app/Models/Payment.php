<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use App\Traits\PaymentTrait;
use Illuminate\Database\Eloquent\Builder;
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

    public const FIELD_AMOUNT_MAP = [
        'enrollment' => 'enrollment_amount',
        'january' => 'january_amount',
        'february' => 'february_amount',
        'march' => 'march_amount',
        'april' => 'april_amount',
        'may' => 'may_amount',
        'june' => 'june_amount',
        'july' => 'july_amount',
        'august' => 'august_amount',
        'september' => 'september_amount',
        'october' => 'october_amount',
        'november' => 'november_amount',
        'december' => 'december_amount',
    ];

    public const STATUS_VALUES = [
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
    ];

    protected $table = "payments";

    protected $casts = [
        'enrollment' => 'integer',
        'january' => 'integer',
        'february' => 'integer',
        'march' => 'integer',
        'april' => 'integer',
        'may' => 'integer',
        'june' => 'integer',
        'july' => 'integer',
        'august' => 'integer',
        'september' => 'integer',
        'october' => 'integer',
        'november' => 'integer',
        'december' => 'integer',
        'enrollment_amount' => 'integer',
        'january_amount' => 'integer',
        'february_amount' => 'integer',
        'march_amount' => 'integer',
        'april_amount' => 'integer',
        'may_amount' => 'integer',
        'june_amount' => 'integer',
        'july_amount' => 'integer',
        'august_amount' => 'integer',
        'september_amount' => 'integer',
        'october_amount' => 'integer',
        'november_amount' => 'integer',
        'december_amount' => 'integer',
    ];

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

    public static function paymentFields(): array
    {
        return array_keys(self::FIELD_AMOUNT_MAP);
    }

    public static function amountFieldFor(string $field): ?string
    {
        return self::FIELD_AMOUNT_MAP[$field] ?? null;
    }

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

    public function scopeByPaymentStatus(Builder $query, $status = null): void
    {
        $query->where(function($q) use($status){
            $q->where('january', $status)
            ->orWhere('february', $status)
            ->orWhere('march', $status)
            ->orWhere('april', $status)
            ->orWhere('may', $status)
            ->orWhere('june', $status)
            ->orWhere('july', $status)
            ->orWhere('august', $status)
            ->orWhere('september', $status)
            ->orWhere('october', $status)
            ->orWhere('november', $status)
            ->orWhere('december', $status);
        });
    }
}
