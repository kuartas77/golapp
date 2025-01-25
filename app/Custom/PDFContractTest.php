<?php

namespace App\Custom;

use App\Traits\PDFTrait;
use App\Models\School;

class PDFContractTest
{
    use PDFTrait;

    public static function makeContract($documentOption, $filename, array $params)
    {
        $class = new self();
        $school = School::find($params['school_id']);
        $player = $class->providePlayerData();

        $playerObject = json_decode(json_encode($player), FALSE);

        $data = [];
        $data['school'] = $school;
        $data['tutor'] = $player['people'][0];
        $data['player'] = $playerObject;
        $data['sign_tutor'] = '10pro-violetas/firma10+pro.jpg';
        $data['sign_player'] = '10pro-violetas/firma10+pro.jpg';

        $class->setWatermarkSize([120, 120]);

        $class->setConfigurationMpdf([
            'format' => 'A4',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 35,
            'margin_bottom' => 20,
            'margin_header' => 4,
            'margin_footer' => 4,
        ]);

        switch ($documentOption) {
            case 1:
                $class->createPDF($data, 'contracts/contract_inscription.blade.php', false);
                break;
            case 2:
                $class->createPDF($data, 'contracts/contract_affiliate.blade.php', false);
                break;
        }

        return $class->stream($filename);
    }

    private function providePlayerData() : array
    {
        return [
            'unique_code' => '',
            'names' => '',
            'last_names' => '',
            'full_names' => '',
            'gender' => 'M',
            'date_birth' => '',
            'place_birth' => '',
            'identification_document' => '',
            'rh' => '',
            'eps' => '',
            'email' => '',
            'address' => '',
            'municipality' => '',
            'neighborhood' => '',
            'zone' => '',
            'commune' => '',
            'phones' => '',
            'mobile' => '',
            'school' => '',
            'degree' => '',
            'category' => '',
            'people' => [
                [
                    "tutor" => "true",
                    "relationship" => "30",
                    "names" => "",
                    "identification_card" => "",
                    "mobile" => "",
                    "email" => "",
                ],
                [
                    "tutor" => "false",
                    "relationship" => "15",
                    "names" => "",
                    "identification_card" => "",
                    "mobile" => "",
                    "email" => "",
                ],
                [
                    "tutor" => "false",
                    "relationship" => "20",
                    "names" => "",
                    "identification_card" => "",
                    "mobile" => "",
                    "email" => "",
                ]
            ]
        ];
    }
}
