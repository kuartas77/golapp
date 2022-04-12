<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;
    
    public const MAX_USERS = 'MAX_USERS';
    public const MAX_GROUPS = 'MAX_GROUPS';
    public const MAX_PLAYERS = 'MAX_PLAYERS';
    public const MAX_INSCRIPTIONS = 'MAX_INSCRIPTIONS';
    public const MAX_REGISTRATION_DATE = 'MAX_REGISTRATION_DATE';
    public const MIN_REGISTRATION_DATE = 'MIN_REGISTRATION_DATE';

    protected $table = "settings";
    protected $fillable = [
        'key', 'public'
    ];

    public function settingsValues(): HasMany
    {
        return $this->hasMany(SettingValue::class, 'setting_key', 'key');
    }
}
