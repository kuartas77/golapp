<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentRequest extends Model
{
    use GeneralScopes;

    protected $table = 'payment_request';
    protected $fillable = [
        'school_id',
        'invoice_id',
        'player_id',
        'amount',
        'description',
        'reference_number',
        'payment_method',
        'image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $appends = ['url_image'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function getUrlImageAttribute(): ?string
    {
        if (!empty($this->attributes['image']) && Storage::disk('public')->exists($this->attributes['image'])) {
            return route('images', $this->attributes['image']);
        }

        return url('img/not-found.png');
    }
}
