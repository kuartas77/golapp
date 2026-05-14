<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceCustomItem extends Model
{
    use HasFactory;
    use GeneralScopes;

    protected $table = "invoice_custom_items";

    protected $fillable = [
        'type',
        'name',
        'unit_price',
        'school_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    protected $appends = ['url_show'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function inscriptionCustomCharges(): HasMany
    {
        return $this->hasMany(InscriptionCustomCharge::class);
    }

    public function getUrlShowAttribute(): string
    {
        return route('invoice-items-custom.show', [$this->attributes['id']]);
    }
}
