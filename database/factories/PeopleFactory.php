<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PeopleFactory extends Factory
{
    public function definition()
    {
        return [
            "tutor" => "true",
            "relationship" => "30",
            "names" => $this->faker->name(),
            "identification_card" => $this->faker->randomNumber(5, true),
            "phone" => $this->faker->phoneNumber(),
        ];
    }
}
