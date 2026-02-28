<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TopicNotification extends Model
{
    use HasFactory;
    use GeneralScopes;

    protected $table = "topic_notifications";

    protected $fillable = [
        'school_id',
        'topics',
        'title',
        'body',
        'image_url',
        'type',
        'priority'
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)->using(PlayerTopicNotification::class)->withPivot(['is_read']);
    }

    public function player_topic_notification(): HasOne
    {
        return $this->hasOne(PlayerTopicNotification::class)->where('is_read', false);
    }
}
