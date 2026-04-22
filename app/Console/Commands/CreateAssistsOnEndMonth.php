<?php

namespace App\Console\Commands;

use App\Models\Assist;
use App\Models\School;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Repositories\AssistRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CreateAssistsOnEndMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assists:month {--date=}';

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
            $currentDate = $this->option('date') ? Carbon::parse($this->option('date')): now();

            if (!$currentDate->isLastOfMonth()) {
                logger(__CLASS__, [$currentDate->format('Y-m-d H:i:s')]);
                return 0;
            }

            $targetDate = $currentDate->copy()->addDay();
            $dataAssist = [
                'year' => $targetDate->year,
                'month' => $targetDate->month,
            ];
            $inscriptionYear = $targetDate->year;

            $schools = School::query()->where('is_enable', true)->get();

            foreach ($schools as $school) {
                $groupsQuery = TrainingGroup::query()
                    ->where('school_id', $school->id)
                    ->whereHas('inscriptions', fn ($query) => $query
                        ->where('school_id', $school->id)
                        ->where('year', $inscriptionYear)
                    );

                $groupsQuery->chunkById(50, function ($groups) use ($dataAssist, $school, $inscriptionYear) {
                    foreach ($groups as $group) {
                        $groupAssistData = [
                            ...$dataAssist,
                            'training_group_id' => $group->id,
                        ];

                        $inscriptionIds = $this->getInscriptionsByGroup($groupAssistData, $school->id, $inscriptionYear);

                        if ($inscriptionIds->isEmpty()) {
                            continue;
                        }

                        $assistsQuery = $this->getAssistQuery($groupAssistData, $school->id);

                        DB::beginTransaction();

                        AssistRepository::createAssistBulk($inscriptionIds, $assistsQuery, $groupAssistData, $school->id);

                        DB::commit();
                    }
                });
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
        }

        return 0;
    }

    private function getInscriptionsByGroup(array $params, int $school_id, int $inscriptionYear): Collection
    {
        return Inscription::query()
            ->where('training_group_id', $params['training_group_id'])
            ->where('year', $inscriptionYear)
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
