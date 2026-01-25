<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlayerTopicNotification extends Pivot
{
    use GeneralScopes;

    public $incrementing = true;

    protected $table = "player_topic_notifications";

    protected $fillable = [
        'topic_notification_id',
        'school_id',
        'player_id',
        'is_read',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
