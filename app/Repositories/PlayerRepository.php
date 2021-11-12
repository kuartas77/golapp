<?php


namespace App\Repositories;


use App\Models\Player;
use Mpdf\MpdfException;
use App\Traits\PDFTrait;
use Jenssegers\Date\Date;
use App\Traits\ErrorTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\Player\PlayerUpdateRequest;

class PlayerRepository
{
    use PDFTrait;
    use ErrorTrait;

    private Player $model;

    private PeopleRepository $peopleRepository;

    public function __construct(Player $model, PeopleRepository $peopleRepository)
    {
        $this->model = $model;
        $this->peopleRepository = $peopleRepository;
    }

    public function getAttributes(): array
    {
        return $this->model->getFillable();
    }

    public function getPlayersPeople()
    {
        return Player::query()->with('peoples')->get();
    }

    public function createPlayer($request): Player
    {
        try {
            DB::beginTransaction();

            $dataPlayer = $this->setAttributes('store', $request);
            $player = $this->model->create($dataPlayer);
            $peopleIds = $this->peopleRepository->getPeopleIds($request->input('people'));
            $player->peoples()->sync($peopleIds);

            DB::commit();

            return $player;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository createPlayer", $exception);
            return $this->model;
        }
    }

    public function updatePlayer(Player $player, PlayerUpdateRequest $request): bool
    {
        try {
            DB::beginTransaction();

            $dataPlayer = $this->setAttributes('update', $request, $player);
            $peopleIds = $this->peopleRepository->getPeopleIds($request->input('people'));
            $player->peoples()->sync($peopleIds);
            $save = $player->fill($dataPlayer)->save();

            DB::commit();

            return $save;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("PlayerRepository updatePlayer", $exception);
            return false;
        }
    }

    public function loadShow(Player $player): Player
    {
        $player->load(['peoples', 'inscriptions' => function ($q) {
            $q->withTrashedRelations();
        }]);
        $player->inscriptions->setAppends(['format_average']);
        return $player;
    }


    /**
     * @throws MpdfException
     */
    public function makePdf(Player $player, bool $stream = true): string
    {
        $player->load('peoples');
        $data['school'] = 'soccercity';
        $data['player'] = $player->load('inscription.trainingGroup','inscription.competitionGroup');
        $filename = "Deportista {$player->unique_code}.pdf";
        $this->setConfigurationMpdf(['format' => [213, 140]]);
        $this->createPDF($data, 'inscription.blade.php');

        if ($stream) {
            return $this->stream($filename);
        }
        return $this->output($filename);
    }


    public function getExcel(): Collection
    {
        $collection = new Collection(['enabled' => collect(), 'disabled' => collect()]);

        $playersEnabled = $this->model->query()->whereHas('inscription')
            ->with(['peoples', 'inscription.trainingGroup.schedule.day', 'payments' => function ($q) {
                $q->withTrashed();
            }])
            ->get();

        $playersDisabled = $this->model->query()->whereDoesntHave('inscription')
            ->with(['peoples', 'inscription.trainingGroup.schedule.day', 'payments' => function ($q) {
                $q->withTrashed();
            }])
            ->get();

        $playersEnabled->setAppends(['tutor_people', 'pay_years']);
        $playersDisabled->setAppends(['tutor_people', 'pay_years']);
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
        return $this->model->query()->where('identification_document', $request->input('doc'))->exists();
    }

    /**
     * @param $request
     * @return bool
     */
    public function checkUniqueCode($request): bool
    {
        return $this->model->query()->withTrashed()->where('unique_code', $request->input('unique_code'))->exists();
    }

    public function searchUniqueCode(array $fields)
    {
        return $this->model->query()->whereDoesntHave('inscription', function ($q) {
            $q->where('year', now());
        })->where('unique_code', $fields['unique_code'])->first();
    }

    /**
     * @param string $method
     * @param $request
     * @param Player|null $player
     * @return mixed
     */
    private function setAttributes(string $method, $request, Player $player = null)
    {
        $file_name = $this->model->fileName($method, $request);
        $dataPlayer = $request->only($this->getAttributes());
        $dataPlayer['photo'] = $file_name;
        $dataPlayer['date_birth'] = Date::parse(request('date_birth'));
        $dataPlayer['unique_code'] = request('unique_code', optional($player)->unique_code);
        return $dataPlayer;
    }
}
