<?php

namespace App\Traits;

trait PaymentTrait
{

    public function getCheckPaymentsAttribute(): int
    {
        $months = [
            'january', 'february', 'march',
            'april', 'may', 'june',
            'july', 'august', 'september',
            'october', 'november', 'december'
        ];

        $value = 0;
        foreach ($months as $month) {
            switch ($this->attributes[$month]) {
                case '2':
                case '9':
                case '10':
                case '11':
                case '12':
                    $value++;
                    break;
                default:
                    break;
            }
        }
        return $value;
    }
}
