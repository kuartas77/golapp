<?php


namespace App\Repositories;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use App\Service\Assist\AssistService;
use App\Traits\ErrorTrait;
use App\Traits\PDFTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class AssistRepository
{
    use PDFTrait;
    use ErrorTrait;

    protected AssistService $service;

    public function __construct(protected Assist $model)
    {
        $this->service = new AssistService();
    }

    /**
     * @param array $data
     * @param false $deleted
     * @return array
     */
    public function search(array $data, bool $deleted = false): array
    {
        if (!$deleted) {
            $data['year'] = now()->year;
        }

        $trainingGroup = TrainingGroup::query()->schoolId()
            ->when($deleted, fn($q) => $q->onlyTrashedRelations())->findOrFail($data['training_group_id']);

        $assists = $this->model->schoolId()->with('inscription.player')
            ->when($deleted, fn($q) => $q->withTrashed())
            ->where($data);

        return $this->service->generateTable($assists, $trainingGroup, $data, $deleted);
    }

    /**
     * @param array $dataAssist
     * @return array
     */
    public function create(array $dataAssist): array
    {
        $table = [];
        try {

            $dataAssist['year'] = now()->year;

            $trainingGroup = TrainingGroup::query()->schoolId()->find($dataAssist['training_group_id']);
            $inscriptionIds = Inscription::query()->schoolId()
                ->where('training_group_id', $dataAssist['training_group_id'])
                ->where('year', $dataAssist['year'])->pluck('id');

            $school_id = getSchool(auth()->user())->id;

            $assists = $this->model->schoolId()->with('inscription.player')->where($dataAssist);

            if ($inscriptionIds->isNotEmpty()) {

                $assistsIds = $assists->pluck('inscription_id');

                $idsDiff = $inscriptionIds->diff($assistsIds);

                DB::beginTransaction();
                foreach ($idsDiff as $id) {
                    $this->model->updateOrCreate(
                        [
                            'inscription_id' => $id,
                            'year' => $dataAssist['year'],
                            'month' => $dataAssist['month'],
                            'training_group_id' => $dataAssist['training_group_id'],
                            'school_id' => $school_id
                        ],
                        [
                            'inscription_id' => $id,
                            'year' => $dataAssist['year'],
                            'month' => $dataAssist['month'],
                            'training_group_id' => $dataAssist['training_group_id'],
                            'school_id' => $school_id
                        ]
                    );
                }
                DB::commit();
            }

            $table = $this->service->generateTable($assists, $trainingGroup, $dataAssist);

        } catch (Exception $th) {
            DB::rollBack();
            $this->logError("AssistRepository create", $th);
        }

        return $table;
    }

    /**
     * @param Assist $assist
     * @param array $validated
     * @return bool
     */
    public function update(Assist $assist, array $validated): bool
    {
        try {
            DB::beginTransaction();
            $updated = $assist->update($validated);
            DB::commit();
            return $updated;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("AssistRepository update", $exception);
            return false;
        }
    }
}
