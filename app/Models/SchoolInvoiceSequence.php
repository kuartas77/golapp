<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolInvoiceSequence extends Model
{
    protected $fillable = ['school_id', 'next_number'];

    protected $casts = ['next_number' => 'integer'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
