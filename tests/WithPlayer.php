<?php

namespace Tests;

use App\Models\Player;

trait WithPlayer
{
    public function dataPlayer(): array
    {
        return [
            'unique_code' => '1111111111',
            'names' => 'juan esteban',
            'last_names' => 'cuartas londoño',
            'gender' => 'M',
            'date_birth' => '1989-02-13',
            'place_birth' => 'Medellin',
            'identification_document' => '1017170333',
            'rh' => 'O+',
            'eps' => 'Sura',
            'email' => 'kuartas77@gmail.com',
            'address' => 'calle falsa 123',
            'municipality' => 'Medellin',
            'neighborhood' => 'Robledo, Pilarica',
            'zone' => '',
            'commune' => '7',
            'phones' => '111222222',
            'mobile' => '113333333',
            'school' => 'Pascual',
            'degree' => '11',
            'people' => [
                [
                    "tutor" => "true",
                    "relationship" => "30",
                    "names" => "CRISTINA VANEGAS",
                    "identification_card" => "3015614556",
                    "phone" => "5961994"
                ]
            ]
        ];
    }
    public function createPlayer():void
    {
        $data = $this->dataPlayer();
        unset($data['people']);

        Player::insert($data);
    }
}
