<?php

namespace App\Traits;

trait PaymentTrait
{

    public function getCheckPaymentsAttribute(): int
    {
        $value = 0;
        $this->attributes['january'] === '2' ? $value++ : 0;
        $this->attributes['february'] === '2' ? $value++ : 0;
        $this->attributes['march'] === '2' ? $value++ : 0;
        $this->attributes['april'] === '2' ? $value++ : 0;
        $this->attributes['may'] === '2' ? $value++ : 0;
        $this->attributes['june'] === '2' ? $value++ : 0;
        $this->attributes['july'] === '2' ? $value++ : 0;
        $this->attributes['august'] === '2' ? $value++ : 0;
        $this->attributes['september'] === '2' ? $value++ : 0;
        $this->attributes['october'] === '2' ? $value++ : 0;
        $this->attributes['november'] === '2' ? $value++ : 0;
        $this->attributes['december'] === '2' ? $value++ : 0;
        return $value;
    }
}
