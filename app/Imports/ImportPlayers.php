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
    private Collection $peopleByName;

    private Collection $peopleByIdentification;

    private Collection $playersByDocument;

    private Collection $activeInscriptionPlayerIds;

    private int $createdPlayers = 0;

    private int $updatedPlayers = 0;

    private int $createdInscriptions = 0;

    private int $skippedInscriptions = 0;

    public function __construct(
        private int $school_id,
        private PlayerRepository $playerRepository,
        private InscriptionRepository $inscriptionRepository
    ) {
        $this->peopleByName = collect();
        $this->peopleByIdentification = collect();
        $this->playersByDocument = collect();
        $this->activeInscriptionPlayerIds = collect();
    }

    public function collection(Collection $rows)
    {
        try {
            $rows = $this->filterBlankRows($rows);
            $this->warmChunkLookups($rows);

            foreach ($rows as $row) {
                DB::beginTransaction();
                $dataPeople = $this->setAttributesPeople($row);

                $people = $this->storePeople($dataPeople);

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
        $player = $this->playersByDocument->get($dataPlayer['identification_document']);

        if (! $player) {
            $player = Player::query()->create($dataPlayer);
            $this->createdPlayers++;
            $this->playersByDocument->put($player->identification_document, $player);

            return $player;
        }

        $player->fill(Arr::except($dataPlayer, ['unique_code']));
        $player->save();
        $this->updatedPlayers++;
        $this->playersByDocument->put($player->identification_document, $player);

        return $player;
    }

    private function createImportedInscription(Player $player): void
    {
        $year = getYearInscription();

        if ($this->activeInscriptionPlayerIds->contains($player->id)) {
            $this->skippedInscriptions++;
            return;
        }

        $result = $this->inscriptionRepository->createInscription([
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

        if (! data_get($result, 'success')) {
            throw new Exception('Imported player inscription could not be created.');
        }

        $this->createdInscriptions++;
        $this->activeInscriptionPlayerIds->push($player->id);
    }

    private function storePeople(array $dataPeople): People
    {
        $people = $this->peopleByIdentification->get($dataPeople['identification_card'])
            ?? $this->peopleByName->get($dataPeople['names']);

        if (! $people) {
            $people = People::query()->create($dataPeople);
        }

        $this->peopleByName->put($people->names, $people);
        if (filled($people->identification_card)) {
            $this->peopleByIdentification->put($people->identification_card, $people);
        }

        return $people;
    }

    private function warmChunkLookups(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $peopleNames = $rows
            ->map(fn ($row) => Str::upper($this->rowValue($row, 'nombres_y_apellidos')))
            ->filter()
            ->unique()
            ->values();

        $peopleIdentifications = $rows
            ->map(fn ($row) => $this->firstRowValue($row, ['numero_de_telefono', 'telefonos', 'numero_de_celular']))
            ->filter()
            ->unique()
            ->values();

        $people = collect();
        if ($peopleNames->isNotEmpty() || $peopleIdentifications->isNotEmpty()) {
            $people = People::query()
                ->select(['id', 'names', 'identification_card'])
                ->where(function ($query) use ($peopleNames, $peopleIdentifications) {
                    $query->when($peopleNames->isNotEmpty(), fn ($query) => $query->whereIn('names', $peopleNames))
                        ->when($peopleIdentifications->isNotEmpty(), fn ($query) => $query->orWhereIn('identification_card', $peopleIdentifications));
                })
                ->get();
        }

        $this->peopleByName = $people->keyBy('names');
        $this->peopleByIdentification = $people->whereNotNull('identification_card')->keyBy('identification_card');

        $documents = $rows
            ->map(fn ($row) => $this->rowValue($row, 'numero_de_documento'))
            ->filter()
            ->unique()
            ->values();

        $players = $documents->isEmpty()
            ? collect()
            : Player::query()
                ->where('school_id', $this->school_id)
                ->whereIn('identification_document', $documents)
                ->get();

        $this->playersByDocument = $players->keyBy('identification_document');

        $this->activeInscriptionPlayerIds = Inscription::query()
            ->where('school_id', $this->school_id)
            ->where('year', getYearInscription())
            ->whereIn('player_id', $players->pluck('id'))
            ->pluck('player_id');
    }

    private function filterBlankRows(Collection $rows): Collection
    {
        return $rows->filter(function ($row) {
            return collect($row)->filter(fn ($value) => filled($value))->isNotEmpty();
        });
    }

    public function summary(): array
    {
        return [
            'created_players' => $this->createdPlayers,
            'updated_players' => $this->updatedPlayers,
            'created_inscriptions' => $this->createdInscriptions,
            'skipped_inscriptions' => $this->skippedInscriptions,
        ];
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
