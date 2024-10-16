<?php


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
            $school_id = $dataPlayer['school_id'];

            if(!isset($dataPlayer['unique_code'])){
                $dataPlayer['unique_code'] = $this->createUniqueCode($dataPlayer['school_id']);
            }

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
            Cache::forget("KEY_LAST_UNIQUE_CODE.{$school_id}");
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
        PlayerExportService::loadClassDays($player);
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

    public function getListPlayersWithInscription(bool $isTrashed = true)
    {
        return $this->model->query()->schoolId()->whereHas('inscription', fn($q) => $q->where('year', now()->year))->pluck('unique_code');
    }

    public function birthdayToday(): Collection
    {
        $school_id = getSchool(auth()->user())->id;
        return Cache::remember("BIRTHDAYS_{$school_id}", Carbon::now()->addDay()->startOfDay(), function () {
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
            'barrio', 'correo_electronico', 'numero_de_celular', 'eps', 'nombres_y_apellidos',
            'numero_de_telefono', 'profesion', 'empresa', 'cargo',
        ]);
        return $headers->diff($headers_validation)->implode(',');
    }

    public function createUniqueCode($school_id): mixed
    {
        $newUniqueCode = '';
        $year = now()->year;

        $lastUniqueCode = Cache::remember("KEY_LAST_UNIQUE_CODE.{$school_id}", now()->addMinute(), function() use($year, $school_id){
            $result = DB::table('players')->select(['unique_code'])->where('unique_code', 'like', "$year%")->where('school_id', $school_id)->orderBy('unique_code', 'desc')->limit(1)->first();
            return isset($result) ? $result->unique_code : null;
        });

        if(isset($lastUniqueCode)){
            $newUniqueCode = intval($lastUniqueCode) + 1;
        }else{
            $count = 1;
            $newUniqueCode = $year . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
        }

        $newUniqueCode = $this->generateCode($school_id, $newUniqueCode);

        Cache::put("KEY_LAST_UNIQUE_CODE.{$school_id}", $newUniqueCode, now()->addMinute());

        return $newUniqueCode;
    }

    private function generateCode($school_id, $lastUniqueCode)
    {
        $next = true;
        while ($next){
            $exits = DB::table('players')->select(['unique_code'])
                    ->where('unique_code', $lastUniqueCode)
                    ->where('school_id', $school_id)
                    ->exists();
            if(!$exits){
                $next = false;
            }else{
                $lastUniqueCode = intval($lastUniqueCode) + 1;
            }
        }
        return $lastUniqueCode;
    }
}
