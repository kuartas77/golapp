<?php

namespace App\Console\Commands;

use App\Models\Assist;
use App\Models\School;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Repositories\AssistRepository;
use Illuminate\Database\Eloquent\Builder;

class CreateAssistsOnEndMonth extends Command
{
    use ErrorTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assists:month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create attendances for the next month when it is the last day of the current month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $dataAssist = [];
            $currentDate = now();

            if ($currentDate->isLastOfMonth()) {

                $dataAssist['year'] = $currentDate->year;
                $dataAssist['month'] = $currentDate->addDay()->month;

                $schools = School::query()->where('is_enable', true)->get();

                foreach ($schools as $school) {
                    $groupsQuery = TrainingGroup::query()->whereHas('inscriptions', fn($q) => $q->where('school_id', $school->id)->where('year', $dataAssist['year']));

                    $groupsQuery->chunkById(2, function ($groups) use ($dataAssist, $school) {
                        foreach ($groups as $group) {

                            $dataAssist['training_group_id'] = $group->id;

                            $inscriptionIds = $this->getInscriptionsByGroup($dataAssist, $school->id);

                            $assistsQuery = $this->getAssistQuery($dataAssist, $school->id);

                            DB::beginTransaction();

                            AssistRepository::createAssistBulk($inscriptionIds, $assistsQuery, $dataAssist, $school->id);

                            DB::commit();
                        }
                    });
                }
            } else {
                logger(__CLASS__, [$currentDate->format('Y-m-d H:i:s')]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError(__FUNCTION__, $th);
        }

        return 0;
    }

    private function getInscriptionsByGroup(array $params, int $school_id): Collection
    {
        return Inscription::query()
            ->where('training_group_id', $params['training_group_id'])
            ->where('year', $params['year'])
            ->where('school_id', $school_id)
            ->pluck('id');
    }

    private function getAssistQuery(array $params, int $school_id): Builder
    {
        return Assist::query()->with('inscription.player')
            ->where('year', $params['year'])
            ->where('training_group_id', $params['training_group_id'])
            ->where('month', $params['month'])
            ->where('school_id', $school_id);
    }
}
