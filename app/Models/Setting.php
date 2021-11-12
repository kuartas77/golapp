<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;
    
    protected $table = "settings";
    protected $fillable = [
        'key', 'public'
    ];

    public function settingsValues(): HasMany
    {
        return $this->hasMany(SettingValue::class, 'setting_key', 'key');
    }
}
