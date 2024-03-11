<?php


namespace App\Repositories;


use App\Http\Requests\Player\PlayerCreateRequest;
use App\Http\Requests\Player\PlayerUpdateRequest;
use App\Models\Master;
use App\Models\Player;
use App\Notifications\RegisterPlayerNotification;
use App\Traits\ErrorTrait;
use App\Traits\PDFTrait;
use App\Traits\UploadFile;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\HeadingRowImport;

class PlayerRepository
{
    use PDFTrait;
    use ErrorTrait;
    use UploadFile;

    public function __construct(private Player $model, private PeopleRepository $peopleRepository)
    {
    }

    public function getPlayersPeople()
    {
        return $this->model->query()->with(['people'])->schoolId()->get();
    }

    public function createPlayer(PlayerCreateRequest $request): bool
    {
        $result = false;
        try {
            $dataPlayer = $request->only($this->getAttributes());

            if ($file_name = $this->saveFile($request, 'player')) {
                $dataPlayer['photo'] = $file_name;
            }
            $dataPlayer = $this->setAttributes($dataPlayer);

            DB::beginTransaction();

            Master::saveAutoComplete($request->all());
            $player = $this->model->create($dataPlayer);

            $dataPeople = $request->input('people', []);
            throw_unless($dataPeople, Exception::class, 'not provide people data.');

            $peopleIds = $this->peopleRepository->getPeopleIds($request->input('people'));
            $player->people()->sync($peopleIds);

            !checkEmail($player->email) ?: $player->notify(new RegisterPlayerNotification($player));

            DB::commit();

            $result = true;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository@createPlayer", $exception);
            $result = false;
        }
        return $result;
    }

    public function getAttributes(): array
    {
        return $this->model->getFillable();
    }

    /**
     * @param string $method
     * @param $request
     * @param Player|null $player
     * @return mixed
     */
    private function setAttributes(array $dataPlayer, Player $player = null)
    {
        $dataPlayer['date_birth'] = Carbon::parse($dataPlayer['date_birth']);
        $dataPlayer['category'] = categoriesName($dataPlayer['date_birth']->year);
        $dataPlayer['unique_code'] = ($dataPlayer['unique_code'] ?? optional($player)->unique_code);
        return $dataPlayer;
    }

    public function updatePlayer(Player $player, PlayerUpdateRequest $request): bool
    {
        try {
            $dataPlayer = $request->only($this->getAttributes());
            if ($file_name = $this->saveFile($request, 'player')) {
                $dataPlayer['photo'] = $file_name;
            }
            $dataPlayer = $this->setAttributes($dataPlayer, $player);

            DB::beginTransaction();

            Master::saveAutoComplete($dataPlayer);
            $dataPeople = $request->input('people', []);

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

    public function loadShow(Player $player): Player
    {
        $player->load(['people', 'inscriptions' => fn($q) => $q->withTrashedRelations()]);
        $player->inscriptions->setAppends(['format_average']);
        return $player;
    }

    /**
     * @param $request
     * @return bool
     */
    public function checkDocumentExists(string $doc): bool
    {
        return $this->model->query()->schoolId()->where('identification_document', $doc)->exists();
    }

    /**
     * @param $request
     * @return bool
     */
    public function checkUniqueCode(string $unique_code): bool
    {
        return $this->model->query()->schoolId()->withTrashed()->where('unique_code', $unique_code)->exists();
    }

    public function searchUniqueCode(array $fields)
    {
        return $this->model->query()->schoolId()->whereDoesntHave('inscription')
            ->firstWhere('unique_code', $fields['unique_code']);
    }

    public function getListPlayersNotInscription(bool $isTrashed = true)
    {
        return $this->model->query()->schoolId()->whereDoesntHave('inscription')->pluck('unique_code');
    }

    public function birthdayToday(): Collection
    {
        $school_id = getSchool(auth()->user())->id;
        return Cache::remember("BIRTHDAYS_{$school_id}", Carbon::now()->addDay(), function () {
            return $this->model->query()->schoolId()->whereHas('inscription')
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
            'barrio', 'numero_de_telefono', 'correo_electronico', 'numero_de_celular', 'eps', 'nombres_y_apellidos',
            'numero_de_celularr', 'numero_de_telefono', 'numero_de_celularr', 'profesion', 'empresa', 'cargo',
        ]);
        return $headers->diff($headers_validation)->implode(',');
    }
}
