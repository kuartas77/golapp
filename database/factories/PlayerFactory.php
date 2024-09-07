<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlayerFactory extends Factory
{
    public function definition()
    {
        return [
            'unique_code' => $this->faker->randomNumber(5, true),
            'names' => $this->faker->name(),
            'last_names' => $this->faker->lastName(),
            'gender' => 'M',
            'date_birth' => $this->faker->date('Y-m-d'),
            'place_birth' => 'Medellin',
            'identification_document' => $this->faker->randomNumber(5, true),
            'rh' => 'O+',
            'eps' => 'Sura',
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'municipality' => 'Medellin',
            'neighborhood' => 'Robledo, Pilarica',
            'zone' => '',
            'commune' => '7',
            'phones' => $this->faker->phoneNumber(),
            'mobile' => $this->faker->phoneNumber(),
            'school' => 'Pascual',
            'degree' => '11',
        ];
    }
}
