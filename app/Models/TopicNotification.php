<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicNotification extends Model
{
    use HasFactory;
    use GeneralScopes;

    protected $table = "topic_notifications";

    protected $fillable = [
        'school_id',
        'topic',
        'title',
        'body',
        'image_url',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
