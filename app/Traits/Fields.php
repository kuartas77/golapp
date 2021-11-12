<?php


namespace App\Traits;

trait Fields
{
    public function getTokenAttribute()
    {
        return csrf_token();
    }
}
