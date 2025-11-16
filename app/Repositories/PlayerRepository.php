<?php

declare(strict_types=1);

namespace App\Repositories;


use Exception;
use Carbon\Carbon;
use App\Models\Master;
use App\Models\Player;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\HeadingRowImport;
use App\Service\Player\PlayerExportService;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\Player\PlayerCreateRequest;
use App\Http\Requests\Player\PlayerUpdateRequest;
use App\Notifications\RegisterPlayerNotification;
use Illuminate\Foundation\Http\FormRequest;

class PlayerRepository
{
    use PDFTrait;
    use ErrorTrait;
    use UploadFile;

    public function __construct(private Player $player, private PeopleRepository $peopleRepository)
    {
    }

    public function getPlayersPeople()
    {
        return $this->player->query()->with(['people'])->schoolId();
    }

    public function createPlayer(PlayerCreateRequest $playerCreateRequest): bool
    {
        $result = false;
        try {
            $dataPlayer = $playerCreateRequest->only($this->getAttributes());
            $school_id = $dataPlayer['school_id'];

            if(!isset($dataPlayer['unique_code'])){
                $dataPlayer['unique_code'] = createUniqueCode($dataPlayer['school_id']);
            }

            if ($file_name = $this->saveFile($playerCreateRequest, 'player')) {
                $dataPlayer['photo'] = $file_name;
            }

            $dataPlayer = $this->setAttributes($dataPlayer);

            DB::beginTransaction();

            Master::saveAutoComplete($playerCreateRequest->all());
            $player = $this->player->create($dataPlayer);

            $dataPeople = $playerCreateRequest->input('people', []);
            throw_unless($dataPeople, Exception::class, 'not provide people data.');

            $peopleIds = $this->peopleRepository->getPeopleIds($playerCreateRequest->input('people'));
            $player->people()->sync($peopleIds);

            !checkEmail($player->email) ?: $player->notify(new RegisterPlayerNotification($player));

            DB::commit();

            $result = true;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository@createPlayer", $exception);
            Cache::forget('KEY_LAST_UNIQUE_CODE.' . $school_id);
            $result = false;
        }

        return $result;
    }

    public function getAttributes(): array
    {
        return $this->player->getFillable();
    }

    /**
     * @param string $method
     * @param $request
     * @param Player|null $player
     */
    private function setAttributes(array $dataPlayer, ?Player $player = null): array
    {
        $dataPlayer['date_birth'] = Carbon::parse($dataPlayer['date_birth']);
        $dataPlayer['category'] = categoriesName($dataPlayer['date_birth']->year);
        $dataPlayer['unique_code'] = ($dataPlayer['unique_code'] ?? optional($player)->unique_code);
        return $dataPlayer;
    }

    public function updatePlayer(Player $player, PlayerUpdateRequest $playerUpdateRequest): bool
    {
        try {
            $dataPlayer = $playerUpdateRequest->only($this->getAttributes());
            if ($file_name = $this->saveFile($playerUpdateRequest, 'player')) {
                $dataPlayer['photo'] = $file_name;
            }

            $dataPlayer = $this->setAttributes($dataPlayer, $player);

            DB::beginTransaction();

            Master::saveAutoComplete($dataPlayer);
            $dataPeople = $playerUpdateRequest->input('people', []);

            throw_unless($dataPeople, Exception::class, 'not provide people data.');

            $peopleIds = $this->peopleRepository->getPeopleIds($dataPeople);
            $player->people()->sync($peopleIds);

            $save = $player->update($dataPlayer);

            DB::commit();

            return $save;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository@updatePlayer", $exception);
            return false;
        }
    }

    public function updatePlayerPortal(Player $player, FormRequest $formRequest): bool
    {
        try {
            DB::beginTransaction();

            $dataPlayer = $formRequest->only($this->getAttributes());

            if ($file_name = $this->saveFile($formRequest, 'photo')) {
                $dataPlayer['photo'] = $file_name;
            }

            $save = $player->update($dataPlayer);

            DB::commit();

            return $save;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository@updatePlayerPortal", $exception);
            return false;
        }
    }

    public function loadShow(Player $player): Player
    {
        $player->load([
            'schoolData',
            'people',
            'inscriptions' => fn ($q) => $q->with(['trainingGroup' => fn($q) => $q->withTrashed()])->withTrashedRelations()
        ]);
        $player->inscriptions->setAppends(['format_average']);
        PlayerExportService::loadClassDays($player);
        return $player;
    }

    /**
     * @param $request
     */
    public function checkDocumentExists(string $doc): bool
    {
        return $this->player->query()->schoolId()->where('identification_document', $doc)->exists();
    }

    /**
     * @param $request
     */
    public function checkUniqueCode(string $unique_code): bool
    {
        return $this->player->query()->schoolId()->withTrashed()->where('unique_code', $unique_code)->exists();
    }

    public function searchUniqueCode(array $fields)
    {
        return $this->player->query()->schoolId()->whereDoesntHave('inscription')
            ->firstWhere('unique_code', $fields['unique_code']);
    }

    public function getListPlayersNotInscription(bool $isTrashed = true)
    {
        $query = request()->input('query');
        return $this->player->query()->where('unique_code', 'LIKE', '%' . $query )
            ->schoolId()
            ->whereDoesntHave('inscription')
            ->pluck('unique_code');
    }

    public function getListPlayersWithInscription(bool $isTrashed = true)
    {
        return $this->player->query()->schoolId()->whereHas('inscription', fn($q) => $q->where('year', now()->year))->pluck('unique_code');
    }

    public function birthdayToday(): Collection
    {
        $school_id = getSchool(auth()->user())->id;
        return Cache::remember('BIRTHDAYS_' . $school_id, Carbon::now()->addDay()->startOfDay(), function () {
            return $this->player->query()->schoolId()->whereHas('inscription')
                ->whereDay('date_birth', Carbon::now()->day)->whereMonth('date_birth', Carbon::now()->month)
                ->get();
        });
    }

    public function validateImport($file)
    {
        $headings = (new HeadingRowImport())->toCollection($file);
        $headers = $headings->first()->first();

        $headers_validation = collect([
            'fecha_de_nacimiento', 'numero_de_documento', 'nombres', 'apellidos', 'genero', 'lugar_de_nacimiento',
            'numero_de_documento', 'rh', 'escuela_o_colegio_donde_estudia', 'direccion_de_residencia', 'municipio',
            'barrio', 'correo_electronico', 'numero_de_celular', 'eps', 'nombres_y_apellidos',
            'numero_de_telefono', 'profesion', 'empresa', 'cargo',
        ]);
        return $headers->diff($headers_validation)->implode(',');
    }

    public function getPlayerInfo(string $doc, $school_id): array
    {
        $player = $this->player->query()
        ->where('identification_document', $doc)
        ->where('school_id', $school_id)
        //->whereDoesntHave('inscription', fn($q) => $q->where('year', getYearInscription()))
        ->first();

        return isset($player) ? [
            'names' => $player->names,
            'last_names' => $player->last_names,
            'date_birth' => $player->date_birth,
            'place_birth' => $player->place_birth,
            'document_type' => $player->document_type,
            'gender' => $player->gender,
            'email' => $player->email,
            'mobile' => $player->mobile,
            'medical_history' => $player->medical_history,
            'address' => $player->address,
            'municipality' => $player->municipality,
            'neighborhood' => $player->neighborhood,
            'rh' => $player->rh,
            'eps' => $player->eps,
            'student_insurance' => $player->student_insurance,
            'school' => $player->school,
            'degree' => $player->degree,
            'jornada' => $player->jornada,
        ] : [];
    }
}
