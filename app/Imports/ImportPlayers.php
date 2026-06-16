<?php

namespace App\Imports;

use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportPlayers implements ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    public function __construct(
        private int $school_id,
        private PlayerRepository $playerRepository,
        private InscriptionRepository $inscriptionRepository
    ) {
        //
    }

    public function collection(Collection $rows)
    {
        try {

            foreach ($rows as $row) {
                DB::beginTransaction();
                $dataPeople = $this->setAttributesPeople($row);

                $people = People::query()->select('id')
                    ->where(function ($query) use ($dataPeople) {
                        $query->where('names', $dataPeople['names'])
                            ->Orwhere('identification_card', $dataPeople['identification_card']);
                    })->first();

                if (! $people) {
                    $people = People::query()->create($dataPeople);
                }

                $dataPlayer = $this->setAttributesPlayer($row);
                $player = $this->storePlayer($dataPlayer);

                $player->people()->sync([$people->id]);
                DB::commit();

                $this->createImportedInscription($player);
            }

        } catch (Exception $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            report($exception);

            throw $exception;
        }
    }

    private function setAttributesPeople($row): array
    {
        $phone = $this->firstRowValue($row, ['numero_de_telefono', 'telefonos', 'numero_de_celular']);

        return [
            'names' => Str::upper($this->rowValue($row, 'nombres_y_apellidos')),
            'identification_card' => $phone,
            'tutor' => true,
            'relationship' => 30,
            'phone' => $phone ?: null,
            'mobile' => $this->rowValue($row, 'numero_de_celular') ?: null,
            'profession' => $this->rowValue($row, 'profesion') ?: null,
            'business' => $this->rowValue($row, 'empresa') ?: null,
            'position' => $this->rowValue($row, 'cargo') ?: null,
        ];
    }

    private function setAttributesPlayer($row): array
    {
        $dateBirth = $this->parseBirthDate($this->rowValue($row, 'fecha_de_nacimiento'));

        return [
            'unique_code' => createUniqueCode($this->school_id),
            'names' => Str::upper($this->rowValue($row, 'nombres')),
            'last_names' => Str::upper($this->rowValue($row, 'apellidos')),
            'gender' => $this->checkGender($this->rowValue($row, 'genero')),
            'date_birth' => $dateBirth->format('Y-m-d'),
            'place_birth' => $this->rowValue($row, 'lugar_de_nacimiento'),
            'identification_document' => $this->rowValue($row, 'numero_de_documento'),
            'rh' => $this->rowValue($row, 'rh'),
            'photo' => null,
            'category' => categoriesName($dateBirth->year),
            'position_field' => null,
            'dominant_profile' => null,
            'school' => $this->rowValue($row, 'escuela_o_colegio_donde_estudia'),
            'degree' => '',
            'address' => $this->rowValue($row, 'direccion_de_residencia'),
            'municipality' => $this->rowValue($row, 'municipio'),
            'neighborhood' => $this->rowValue($row, 'barrio'),
            'zone' => null,
            'commune' => null,
            'phones' => $this->firstRowValue($row, ['numero_de_telefono', 'telefonos']),
            'email' => $this->rowValue($row, 'correo_electronico'),
            'mobile' => $this->rowValue($row, 'numero_de_celular') ?: null,
            'eps' => $this->rowValue($row, 'eps'),
            'school_id' => $this->school_id,
        ];

    }

    private function checkGender(string $gender): string
    {
        $gender = strtolower(trim($gender));
        if ($gender == 'masculino' || $gender == 'm') {
            $gender = 'M';
        } else {
            $gender = 'F';
        }

        return $gender;
    }

    private function storePlayer(array $dataPlayer): Player
    {
        $player = Player::query()
            ->where('school_id', $dataPlayer['school_id'])
            ->where('identification_document', $dataPlayer['identification_document'])
            ->first();

        if (! $player) {
            return Player::query()->create($dataPlayer);
        }

        $player->fill(Arr::except($dataPlayer, ['unique_code']));
        $player->save();

        return $player;
    }

    private function createImportedInscription(Player $player): void
    {
        $year = getYearInscription();

        $hasActiveInscription = Inscription::query()
            ->where('player_id', $player->id)
            ->where('school_id', $this->school_id)
            ->where('year', $year)
            ->exists();

        if ($hasActiveInscription) {
            return;
        }

        $this->inscriptionRepository->createInscription([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'school_id' => $this->school_id,
            'year' => $year,
            'start_date' => now()->format('Y-m-d'),
            'category' => $player->category,
            'competition_groups' => [],
            'photos' => false,
            'copy_identification_document' => false,
            'eps_certificate' => false,
            'medic_certificate' => false,
            'study_certificate' => false,
            'overalls' => false,
            'ball' => false,
            'bag' => false,
            'presentation_uniform' => false,
            'competition_uniform' => false,
            'tournament_pay' => false,
            'scholarship' => false,
            'pre_inscription' => false,
            'brother_payment' => false,
        ]);
    }

    private function rowValue($row, string $key): string
    {
        $value = data_get($row, $key, '');

        return is_string($value) ? trim($value) : trim((string) $value);
    }

    private function firstRowValue($row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $this->rowValue($row, $key);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function parseBirthDate(string $value): Carbon
    {
        return is_numeric($value)
            ? Carbon::parse(Date::excelToDateTimeObject($value))
            : Carbon::parse($value);
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
}
