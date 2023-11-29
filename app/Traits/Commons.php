<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Commons
{
    /**
     * @param $name
     * @param null $data
     * @param int $seconds
     *
     * @return mixed $data
     */
    public function cacheData($name, $data = null, $seconds = 3000)
    {
        if (Cache::has($name)) {
            return Cache::get($name);
        } else {
            return Cache::remember($name, $seconds, function () use ($data) {
                return $data;
            });
        }
    }

    public function deleteCacheData($key)
    {
        if (Cache::has($key)) {
            Cache::forget($key);
        }
    }
}
