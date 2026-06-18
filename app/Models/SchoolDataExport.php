<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolDataExport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'school_id',
        'requested_by',
        'status',
        'disk',
        'path',
        'filename',
        'size_bytes',
        'manifest',
        'error_message',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'manifest' => 'array',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isReadyForDownload(): bool
    {
        return $this->status === self::STATUS_READY
            && ! $this->isExpired()
            && filled($this->disk)
            && filled($this->path);
    }
}
