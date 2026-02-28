<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingValue extends Model
{
    use HasFactory;

    protected $table = "setting_values";

    protected $fillable = [
        'setting_key', 'school_id', 'value'
    ];

    /**
     * @param int $school_id assign settings to school
     */
    public static function settingsDefault(int $school_id): array
    {
        return [
            [
                'setting_key' => Setting::MAX_USERS,
                'school_id' => $school_id,
                'value' => 2
            ],
            [
                'setting_key' => Setting::MAX_GROUPS,
                'school_id' => $school_id,
                'value' => 5
            ],
            [
                'setting_key' => Setting::MAX_PLAYERS,
                'school_id' => $school_id,
                'value' => 20
            ],
            [
                'setting_key' => Setting::MAX_INSCRIPTIONS,
                'school_id' => $school_id,
                'value' => 20
            ],
            [
                'setting_key' => Setting::INSCRIPTION_AMOUNT,
                'school_id' => $school_id,
                'value' => 70000
            ],
            [
                'setting_key' => Setting::MONTHLY_PAYMENT,
                'school_id' => $school_id,
                'value' => 50000
            ],
            [
                'setting_key' => Setting::NOTIFY_PAYMENT_DAY,
                'school_id' => $school_id,
                'value' => 16
            ],
            [
                'setting_key' => Setting::ANNUITY,
                'school_id' => $school_id,
                'value' => 48333
            ],
            [
                'setting_key' => Setting::SYSTEM_NOTIFY,
                'school_id' => $school_id,
                'value' => false
            ]
        ];
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class, 'setting_key', 'key');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
