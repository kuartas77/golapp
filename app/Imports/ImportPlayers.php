<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\People;
use App\Models\Player;
use App\Traits\ErrorTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ImportPlayers implements ToCollection, WithValidation, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    use ErrorTrait;

    public function __construct(private int $school_id)
    {
        // 
    }
    
    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();
            foreach ($rows as $row) {

                $dataPeople = $this->setAttributesPeople($row);

                $people = People::query()->select('id')
                    ->where(function($query) use($dataPeople){
                        $query->where('names', $dataPeople['names'])
                        ->Orwhere('identification_card', $dataPeople['identification_card']);
                    })->first();

                if(!$people){
                    $people = People::query()->create($dataPeople);
                }
                
                $dataPlayer = $this->setAttributesPlayer($row);
                $player = Player::query()->updateOrCreate(
                    [
                        'unique_code' => $dataPlayer['unique_code'],
                        'school_id' => $dataPlayer['school_id']
                    ],
                    $dataPlayer
                );
                
                $player->people()->sync([$people->id]);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository@createPlayer", $exception);
        }
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function rules(): array
    {
        return [
            // 'administrador' => 'required|string',
        ];
    }

    private function setAttributesPlayer($row): array
    {
        $dateBirth = Carbon::parse(Date::excelToDateTimeObject($row['fecha_de_nacimiento']));

        return [
            'unique_code' => trim($row['numero_de_documento']),
            'names' => Str::upper(trim($row['nombres'])),
            'last_names' => Str::upper(trim($row['apellidos'])),
            'gender' => $this->checkGender($row['genero']),
            'date_birth' => $dateBirth->format('Y-m-d'),
            'place_birth' => trim($row['lugar_de_nacimiento']),
            'identification_document' => trim($row['numero_de_documento']),
            'rh' => trim($row['rh']),
            'photo' => null,
            'category' => categoriesName($dateBirth->year),
            'position_field' => null,
            'dominant_profile' => null,
            'school' => trim($row['escuela_o_colegio_donde_estudia']),
            'degree' => null,
            'address' => trim($row['direccion_de_residencia']),
            'municipality' => trim($row['municipio']),
            'neighborhood' => trim($row['barrio']),
            'zone' => null,
            'commune' => null,
            'phones' => trim(($row['numero_de_telefono'] ?? $row['numero_de_celular'])),
            'email' => trim($row['correo_electronico']),
            'mobile' => trim($row['numero_de_celular']),
            'eps' => trim($row['eps']),
            'school_id' => $this->school_id,
        ];
        
    }

    private function checkGender(string $gender): string
    {
        $gender = strtolower(trim($gender));
        if($gender == 'masculino' || $gender == 'm'){
            $gender = 'M';
        }else{
            $gender = 'F';
        }
        return $gender;
    }

    private function setAttributesPeople($row): array
    {
        return [
            'names' => Str::upper(trim($row['nombres_y_apellidos'])),
            'identification_card' => trim($row['numero_de_celularr']),
            'tutor' => true,
            'relationship' => 30,
            'phone' => trim($row['numero_de_telefono']) ?? null,
            'mobile' => trim($row['numero_de_celularr']) ?? null,
            'profession' => trim($row['profesion']) ?? null,
            'business' => trim($row['empresa']) ?? null,
            'position' => trim($row['cargo']) ?? null,
        ];
    }
}