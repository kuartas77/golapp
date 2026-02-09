<?php

namespace App\Custom;

use App\Traits\UploadFile;
use Illuminate\Foundation\Testing\WithFaker;

class FakerTester
{
    use WithFaker;
    use UploadFile;

    public function __construct()
    {
        $this->setUpFaker();
    }

    public function identification()
    {
        return $this->faker()->unique()->randomNumber(8);
    }

    public function payment_method()
    {
        return $this->faker()->randomElement(['CASH','CARD','TRANSFER','CHECK','OTHER']);
    }

    public function status()
    {
        return $this->faker()->randomElement(['PENDING', 'PARTIAL', 'PAID', 'CANCELLED']);
    }
}
