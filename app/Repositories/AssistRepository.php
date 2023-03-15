<?php


namespace App\Repositories;

use Exception;
use App\Models\Assist;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class AssistRepository
{
    use PDFTrait;
    use ErrorTrait;

    /**
     * @var Assist
     */
    private Assist $model;

    public function __construct(Assist $model)
    {
        $this->model = $model;
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
            ->when($deleted, fn ($q) => $q->onlyTrashedRelations())->findOrFail($data['training_group_id']);

        $assists = $this->model->schoolId()->with('inscription.player')
            ->when($deleted, fn ($q) => $q->withTrashed())
            ->where($data);

        return $this->generateTable($assists, $trainingGroup, $data, $deleted);
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

            $table = $this->generateTable($assists, $trainingGroup, $dataAssist);
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

    /**
     * @param $assists
     * @param $trainingGroup
     * @param array $data
     * @param bool $deleted
     * @return array
     */
    private function generateTable($assists, $trainingGroup, array $data, bool $deleted = false): array
    {
        $months = config('variables.KEY_MONTHS_INDEX');
        $group_name = $trainingGroup->full_schedule_group;
        $assists = $assists->get();

        $classDays = classDays(
            $data['year'],
            array_search($data['month'], $months, true),
            array_map('dayToNumber', $trainingGroup->explode_name['days'])
        );

        $rows = '';
        foreach ($assists as $assist) {
            $rows .= View::make('templates.assists.row', [
                'assist' => $assist,
                'classDays' => $classDays->count(),
                'deleted' => $deleted
            ])->render();
        }

        $thead = View::make('templates.assists.thead', ['classDays' => $classDays])->render();
        $table = View::make('templates.assists.table', ['thead' => $thead, 'rows' => $rows])->render();

        $params = [
            'training_group_id' => $data['training_group_id'],
            'year' => $data['year'],
            'month' => $data['month'],
            'deleted' => $deleted
        ];

        $urlPrint = route('export.pdf.assists', $params);

        $urlPrintExcel = route('export.assists', $params);

        return [
            'table' => $table,
            'group_name' => $group_name,
            'count' => $assists->count(),
            'url_print' => $urlPrint,
            'url_print_excel' => $urlPrintExcel
        ];
    }
}
