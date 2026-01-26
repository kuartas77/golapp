<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Database\Eloquent\Builder;
use Exception;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;
use App\Service\Assist\AssistService;
use App\Models\TrainingGroup;
use App\Models\Inscription;
use App\Models\Assist;
use App\Dto\AssistDTO;

class AssistRepository
{
    use PDFTrait;
    use ErrorTrait;

    protected AssistService $service;

    public function __construct(protected Assist $assist)
    {
        $this->service = new AssistService();
    }

    /**
     * @param false $deleted
     */
    public function search(array $params, bool $deleted = false, bool $raw = false): array
    {
        if (!$deleted) {
            $params['year'] = now()->year;
        }

        $params['month'] = getMonthNumber($params['month']);

        $trainingGroup = TrainingGroup::query()->schoolId()
            ->when($deleted, fn($q) => $q->onlyTrashedRelations())->findOrFail($params['training_group_id']);

        $assists = $this->assist->schoolId()
            ->with('inscription:id,player_id,category', 'inscription.player:id,names,last_names,unique_code,category')
            ->when($deleted, fn($q) => $q->withTrashed())
            ->where([
                ['training_group_id', $params['training_group_id']],
                ['month',  $params['month']],
                ['year', $params['year']]
            ]);

        if(!$raw) {
            return $this->service->generateTable($assists, $trainingGroup, $params, $deleted);
        }else {
            return $this->service->generateData($assists, $trainingGroup, $params, $deleted);
        }
    }

    public function create(array $dataAssist): array
    {
        $table = [];
        try {
            $school_id = getSchool(auth()->user())->id;

            $dataAssist['year'] = now()->year;
            $dataAssist['month'] = getMonthNumber($dataAssist['month']);
            $training_group_id = TrainingGroup::query()->orderBy('id')
                ->firstWhere('school_id', $school_id)->id;

            if ($training_group_id == $dataAssist['training_group_id']) {
                return $table;
            }

            $trainingGroup = TrainingGroup::query()->schoolId()->find($dataAssist['training_group_id']);
            $inscriptionIds = Inscription::query()->schoolId()
                ->where('training_group_id', $dataAssist['training_group_id'])
                ->where('year', $dataAssist['year'])->pluck('id');


            $assistsQuery = $this->assist->schoolId()->with('inscription.player')->where($dataAssist);

            DB::beginTransaction();

            self::createAssistBulk($inscriptionIds, $assistsQuery, $dataAssist, $school_id);

            DB::commit();

            $table = $this->service->generateTable($assistsQuery, $trainingGroup, $dataAssist);
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("AssistRepository create", $exception);
        }

        return $table;
    }

    public function upsert(AssistDTO $assistDto): bool
    {
        try {
            DB::beginTransaction();

            $assist = Assist::query()
                ->where('inscription_id', $assistDto->inscription_id)
                ->where('year', $assistDto->year)
                ->where('month', $assistDto->month)
                ->where('school_id', $assistDto->school_id)
                ->where('training_group_id', $assistDto->training_group_id)
                ->first();

            if($assist) {

                $assist->{$assistDto->column} = $assistDto->value;

                if (isset($assistDto->observations) && isset($assistDto->attendance_date)) {
                    $observations = $assist->observations ?: new \stdClass;

                    $observations->{$assistDto->attendance_date} = $assistDto->observations;

                    $assist->observations = $observations;
                }

                $assist->save();

                DB::commit();

                Cache::delete("statistics.groups.user." . auth()->user()->id);
            }

            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("AssistRepository store", $exception);
            return false;
        }
    }

    public function update(Assist $assist, array $validated): bool
    {
        try {

            DB::beginTransaction();
            if ($assist->observations || (isset($validated['observations']) && isset($validated['attendance_date']))) {
                if ($assist->observations !== null && is_object($assist->observations)) {
                    $observations = $assist->observations;
                } else {
                    $observations = new \stdClass;
                }

                if(isset($validated['attendance_date'])) {
                    $observations->{$validated['attendance_date']} = $validated['observations'];
                    $validated['observations'] = $observations;
                }
            }

            $updated = $assist->update($validated);
            DB::commit();
            return $updated;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("AssistRepository update", $exception);
            return false;
        }
    }

    public static function createAssistBulk(SupportCollection $supportCollection, Builder $builder, array $dataAssist, int $school_id): void
    {
        if ($supportCollection->isNotEmpty()) {

            $assistInscriptionIds = $builder->pluck('inscription_id');

            $idsDiff = $supportCollection->diff($assistInscriptionIds);

            foreach ($idsDiff as $idDiff) {
                Assist::query()->updateOrCreate(
                    [
                        'inscription_id' => $idDiff,
                        'year' => $dataAssist['year'],
                        'month' => $dataAssist['month'],
                        'training_group_id' => $dataAssist['training_group_id'],
                        'school_id' => $school_id
                    ],
                    [
                        'inscription_id' => $idDiff,
                        'year' => $dataAssist['year'],
                        'month' => $dataAssist['month'],
                        'training_group_id' => $dataAssist['training_group_id'],
                        'school_id' => $school_id
                    ]
                );

                Assist::query()->where('inscription_id', $idDiff)
                    ->where('year', $dataAssist['year'])
                    ->where('month', $dataAssist['month'])
                    ->where('training_group_id', '<>', $dataAssist['training_group_id'])
                    ->where('school_id', $school_id)
                    ->forceDelete();
            }
        }
    }
}
