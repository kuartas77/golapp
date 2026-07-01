<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setting extends Model
{
    use HasFactory;

    public const MAX_USERS = 'MAX_USERS';

    public const MAX_GROUPS = 'MAX_GROUPS';

    public const MAX_PLAYERS = 'MAX_PLAYERS';

    public const MAX_INSCRIPTIONS = 'MAX_INSCRIPTIONS';

    public const MAX_REGISTRATION_DATE = 'MAX_REGISTRATION_DATE';

    public const MIN_REGISTRATION_DATE = 'MIN_REGISTRATION_DATE';

    public const INSCRIPTION_AMOUNT = 'INSCRIPTION_AMOUNT';

    public const MONTHLY_PAYMENT = 'MONTHLY_PAYMENT';

    public const BROTHER_MONTHLY_PAYMENT = 'BROTHER_MONTHLY_PAYMENT';

    public const MONTHLY_PAYMENT_OPTION_1 = 'MONTHLY_PAYMENT_OPTION_1';

    public const MONTHLY_PAYMENT_OPTION_2 = 'MONTHLY_PAYMENT_OPTION_2';

    public const MONTHLY_PAYMENT_OPTION_3 = 'MONTHLY_PAYMENT_OPTION_3';

    public const NOTIFY_PAYMENT_DAY = 'NOTIFY_PAYMENT_DAY';

    public const ANNUITY = 'ANNUITY';

    public const SYSTEM_NOTIFY = 'SYSTEM_NOTIFY';

    public const MULTIPLE_SCHOOLS = 'MULTIPLE_SCHOOLS';

    public const INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED = 'INSTRUCTOR_MONTH_LOCK_ENABLED';

    protected $table = "settings";

    protected $fillable = [
        'key', 'public'
    ];

    public static function monthlyPaymentTypes(): array
    {
        return [
            self::MONTHLY_PAYMENT,
            self::BROTHER_MONTHLY_PAYMENT,
            self::MONTHLY_PAYMENT_OPTION_1,
            self::MONTHLY_PAYMENT_OPTION_2,
            self::MONTHLY_PAYMENT_OPTION_3,
        ];
    }

    public function settingsValues(): HasMany
    {
        return $this->hasMany(SettingValue::class, 'setting_key', 'key');
    }
}
