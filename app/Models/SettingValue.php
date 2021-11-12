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
}
