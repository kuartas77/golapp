<?php


namespace App\Repositories;


use Carbon\Carbon;
use App\Models\Master;
use App\Models\Player;
use Mpdf\MpdfException;
use App\Traits\PDFTrait;
use Jenssegers\Date\Date;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;
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
    {}

    public function getAttributes(): array
    {
        return $this->model->getFillable();
    }

    public function getPlayersPeople()
    {
        return $this->model->query()->with('people')->schoolId()->get();
    }

    public function createPlayer(PlayerCreateRequest $request): Player
    {
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($request);
            $dataPlayer = $this->setAttributes($request);
            $player = $this->model->create($dataPlayer);
            $peopleIds = $this->peopleRepository->getPeopleIds($request->input('people'));
            $player->people()->sync($peopleIds);

            $player->notify(new RegisterPlayerNotification($player));
            
            DB::commit();

            return $player;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository@createPlayer", $exception);
            return $this->model;
        }
    }

    public function updatePlayer(Player $player, PlayerUpdateRequest $request): bool
    {
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($request);
            $dataPlayer = $this->setAttributes($request, $player);
            $peopleIds = $this->peopleRepository->getPeopleIds($request->input('people'));
            $player->peoples()->sync($peopleIds);
            $save = $player->update($dataPlayer);

            DB::commit();

            return $save;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository@updatePlayer", $exception);
            return false;
        }
    }

    public function loadShow(Player $player): Player
    {
        $player->load(['people', 'inscriptions' => fn ($q) => $q->withTrashedRelations() ]);
        $player->inscriptions->setAppends(['format_average']);
        return $player;
    }


    /**
     * @throws MpdfException
     */
    public function makePdf(Player $player, bool $stream = true): mixed
    {
        $player->load(['schoolData', 'people','inscription' => fn($query) => $query->with(['trainingGroup','competitionGroup'])]);

        $data['player'] = $player;
        $data['school'] = $player->schoolData;
        $filename = "Deportista {$player->unique_code}.pdf";
        
        $this->setConfigurationMpdf(['format' => [213, 140]]);        
        $this->createPDF($data, 'inscription.blade.php');

        return $stream ? $this->stream($filename) : $this->output($filename);
    }


    public function getExcel(): Collection
    {
        $collection = new Collection(['enabled' => collect(), 'disabled' => collect()]);

        $playersEnabled = $this->model->query()->schoolId()
        ->whereHas('inscription')
        ->with([
            'inscription.trainingGroup.schedule.day', 
            'people' => fn($query)=> $query->where('is_tutor', true),
            'payments' => fn ($q) => $q->withTrashed() 
        ])->get();

        $playersDisabled = $this->model->query()->schoolId()
        ->whereDoesntHave('inscription')
        ->with([
            'inscription.trainingGroup.schedule.day', 
            'people' => fn($query)=> $query->where('is_tutor', true),
            'payments' => fn ($q) => $q->withTrashed() 
        ])->get();

        $collection['enabled'] = $playersEnabled;
        $collection['disabled'] = $playersDisabled;

        return $collection;
    }

    /**
     * @param $request
     * @return bool
     */
    public function checkDocumentExists($request): bool
    {
        return $this->model->query()->schoolId()->where('identification_document', $request->input('doc'))->exists();
    }

    /**
     * @param $request
     * @return bool
     */
    public function checkUniqueCode($request): bool
    {
        return $this->model->query()->schoolId()->withTrashed()->where('unique_code', $request->input('unique_code'))->exists();
    }

    public function searchUniqueCode(array $fields)
    {
        return $this->model->query()->schoolId()->whereDoesntHave('inscription', fn ($q) => $q->where('year', now()->year))
        ->firstWhere('unique_code', $fields['unique_code']);
    }

    public function getListPlayersNotInscription(bool $isTrashed = true)
    {
        $enabled = $this->model->query()->schoolId()->select(['id','unique_code'])->whereHas('inscription')->get();

        if ($isTrashed) {
            $players = $this->model->query()->schoolId()->whereNotIn('id', $enabled->pluck('id'))->pluck('unique_code');
        } else {
            $players = $enabled->pluck('unique_code');
        }
        return $players;
    }

    /**
     * @param string $method
     * @param $request
     * @param Player|null $player
     * @return mixed
     */
    private function setAttributes(FormRequest $request, Player $player = null)
    {
        $dataPlayer = $request->only($this->getAttributes());
        if($file_name = $this->saveFile($request, 'player')){
            $dataPlayer['photo'] = $file_name;
        }
        $dataPlayer['date_birth'] = Date::parse(request('date_birth'));
        $dataPlayer['category'] = categoriesName(Date::parse(request('date_birth')->year));
        $dataPlayer['unique_code'] = request('unique_code', optional($player)->unique_code);
        return $dataPlayer;
    }

    public function birthdayToday(): Collection
    {
        return Cache::remember('BIRTHDAYS', Carbon::now()->addDay(), function(){
            return $this->model->query()->schoolId()->whereHas('inscription')
            ->whereDay('date_birth', Carbon::now()->day)->whereMonth('date_birth', Carbon::now()->month)
            ->get();
        });
    }
}
