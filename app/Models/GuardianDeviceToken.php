<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardianDeviceToken extends Model
{
    protected $fillable = [
        'people_id',
        'platform',
        'token',
    ];

    protected $hidden = [
        'token',
    ];

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(People::class, 'people_id');
    }
}
