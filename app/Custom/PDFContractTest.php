<?php

namespace App\Custom;

use Illuminate\Support\Str;
use App\Traits\PDFTrait;
use App\Models\School;
use App\Models\Contract;

class PDFContractTest
{
    use PDFTrait;

    public static function makeContract($documentOption, $filename, array $params)
    {
        $class = new self();
        $player = $class->providePlayerData();
        $school = School::find($params['school_id']);
        $contract = Contract::where('contract_type_id', $documentOption)->firstWhere('school_id', $params['school_id']);

        $playerObject = json_decode(json_encode($player), FALSE);

        $variables = [];
        $variables['SCHOOL_LOGO'] = $school->logo_local;;
        $variables['SCHOOL_NAME'] = Str::upper($school->name);
        $variables['TUTOR_NAME'] = data_get($player, 'people.0.names', '');
        $variables['TUTOR_DOC'] = data_get($player, 'people.0.identification_card', '');
        $variables['PLAYER_FULLNAMES'] = $playerObject->full_names;
        $variables['IMAGE_ONE'] = storage_path("app/public/".'10pro-violetas/firma10+pro.jpg');
        $variables['IMAGE_TWO'] = storage_path("app/public/".'10pro-violetas/firma10+pro.jpg');
        $variables['IMAGE_THREE'] = storage_path("app/public/".'10pro-violetas/firma10+pro.jpg');
        $variables['SIGN_TUTOR'] = storage_path("app/public/".'10pro-violetas/firma10+pro.jpg');
        $variables['SCHOOL_NAMES'] = Str::upper($school->name);
        $variables['SIGN_PLAYER'] = storage_path("app/public/".'10pro-violetas/firma10+pro.jpg');
        $variables['DAY'] = now()->format('d');
        $variables['MONTH'] = config('variables.KEY_MONTHS_INDEX')[now()->month];
        $variables['YEAR'] = now()->format('Y');
        $variables['DATE'] = now()->format('d-m-Y');

        $variables['PLAYER_DOC'] = $playerObject->identification_document;
        $variables['PLAYER_DATE_BIRTH'] = $playerObject->date_birth;
        $variables['PLAYER_ADDRESS'] = $playerObject->address;
        $variables['PLAYER_EPS'] = $playerObject->eps;
        $variables['CATEGORY'] = $playerObject->category;
        $variables['MOTHER_NAMES'] = '';
        $variables['MOTHER_MOBILE'] = '';
        $variables['MOTHER_EMAIL'] = '';
        $variables['DAD_NAMES'] = '';
        $variables['DAD_MOBILE'] = '';
        $variables['DAD_EMAIL'] = '';
        $variables['SCHOOL_AGENT'] = $school->agent;
        $variables['SCHOOL_SIGN'] = storage_path("app/public/".'10pro-violetas/firma10+pro.jpg');

        // {{config('variables.KEY_RELATIONSHIPS_SELECT')[$people->relationship]}}

        $params = explode(",", str_replace(['[',']'], ['', ''], $contract->parameters));

        $header = $contract->header;
        $body = $contract->body;
        $footer = $contract->footer;

        foreach($params as $param) {
            if(isset($variables[$param])){
                $header = str_replace('['.$param.']', $variables[$param], $header);
                $body = str_replace('['.$param.']', $variables[$param], $body);
                $footer = str_replace('['.$param.']', $variables[$param], $footer);
            }
        }

        $data = [];
        $data['school'] = $school;
        $data['header'] = $header;
        $data['body'] = $body;
        $data['footer'] = $footer;


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
            'full_names' => 'cuartas londoño esteban',
            'gender' => 'M',
            'date_birth' => '13-02-1989',
            'place_birth' => '',
            'identification_document' => '89021727',
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
            'category' => '1989',
            'people' => [
                [
                    "tutor" => "true",
                    "relationship" => "30",
                    "names" => "juan cuartas",
                    "identification_card" => "1017170333",
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
