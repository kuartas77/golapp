<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $table = "configurations";

    protected $fillable = [
        'options',
    ];

    protected $casts = [
        'options' => 'array'
    ];

    public function setOptionsAttribute($value): void
    {
        $options = [];

        foreach ($value as $array_item) {
            if (!is_null($array_item['key'])) {
                $options[] = $array_item;
            }
        }

        $this->attributes['options'] = json_encode($options);
    }
}
