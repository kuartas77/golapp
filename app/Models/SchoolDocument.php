<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolDocument extends Model
{
    use HasFactory;

    public const SCOPE_CLUB = 'club_document';
    public const SCOPE_PLANNING = 'document_planning';

    protected $fillable = [
        'school_id', 'uploaded_by', 'scope', 'title', 'description', 'disk', 'path',
        'original_name', 'mime_type', 'extension', 'size_bytes',
    ];

    protected $casts = ['size_bytes' => 'integer'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
