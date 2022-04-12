<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SettingValue extends Model
{
    use HasFactory;
    
    protected $table = "setting_values";
    protected $fillable = [
        'setting_key', 'school_id', 'value'
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class, 'setting_key', 'key');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

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
        ];
    }
}
