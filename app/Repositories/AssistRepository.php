<?php


namespace App\Repositories;


use Exception;
use App\Models\Assist;
use Mpdf\MpdfException;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Repositories\Contracts\AssistRepositoryContract;

class AssistRepository implements AssistRepositoryContract
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
     * @param $params
     * @param false $deleted
     * @return array
     */
    public function dataExport($params, bool $deleted = false): array
    {
        $months = config('variables.KEY_MONTHS_INDEX');
        if ($deleted) {
            $group = TrainingGroup::onlyTrashedRelations()
                ->find($params['training_group_id']);

            $assists = $this->model->onlyTrashedRelations()->where([
                'training_group_id' => $params['training_group_id'],
                'month' => $params['month'], 'year' => $params['year']
            ])->get();
        } else {
            $group = TrainingGroup::with('schedule.day', 'professor:id,name')
                ->find($params['training_group_id']);

            $assists = $this->model->with('inscription.player')->where([
                'training_group_id' => $params['training_group_id'],
                'month' => $params['month'], 'year' => $params['year']
            ])->get();
        }

        $classDays = classDays(
            $params['year'],
            array_search($params['month'], $months, true),
            array_map('dayToNumber', $group->explode_name['days'])
        );

        return [$assists, $classDays, $group->full_schedule_group, $group];
    }

    /**
     * @param $params
     * @param $deleted
     * @return mixed
     * @throws MpdfException
     */
    public function generatePDF($params, $deleted)
    {
        list($assists, $classDays, $group_name, $group) = $this->dataExport($params, $deleted);

        $data['school'] = 'soccercity';
        $data['assists'] = $assists;
        $data['count'] = $assists->count() + 1;
        $data['result'] = (40 - $data['count']);
        $data['classDays'] = $classDays;
        $data['group'] = $group;
        $data['group_name'] = $group_name;
        $data['month'] = $params['month'];
        $data['year'] = $params['year'];
        $data['optionAssist'] = config('variables.KEY_ASSIST_LETTER');

        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'assists.blade.php');

        return $this->stream("{$group_name}.pdf");
    }

    /**
     * @param $request
     * @return array
     */
    public function create($request): array
    {
        $trainingGroup = TrainingGroup::query()->with('schedule.day')->find($request->input('training_group_id'));
        $inscriptionIds = Inscription::query()->where('training_group_id', $request->input('training_group_id'))
            ->where('year', now()->year)
            ->pluck('id');

        $request->merge(['year' => now()->year]);

        $assists = $this->model->with('inscription.player')
            ->where($request->only(['training_group_id', 'year', 'month']));

        $assistsIds = $assists->pluck('inscription_id');

        if ($inscriptionIds->isNotEmpty()) {

            $idsDiff = $inscriptionIds->diff($assistsIds);

            try {
                DB::beginTransaction();
                foreach ($idsDiff as $id) {
                
                    $this->model->updateOrCreate(
                        [
                            'inscription_id' => $id,
                            'year' => $request->input('year'),
                            'month' => $request->input('month'),
                        ],
                        [
                            'inscription_id' => $id,
                            'year' => $request->input('year'),
                            'month' => $request->input('month'),
                            'training_group_id' => $request->input('training_group_id'),
                        ]
                    );
                }
                DB::commit();
            } catch (Exception $th) {
                DB::rollBack();
                $this->logError("AssistRepository create", $th);
            }
        }

        return $this->generateTable($assists, $trainingGroup, $request);
    }

    /**
     * @param $assist
     * @param $request
     * @return bool
     */
    public function update($assist, $request): bool
    {
        try {
            DB::beginTransaction();
            $updated = $assist->update($request->validated());
            DB::commit();
            return $updated;

        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("AssistRepository update", $exception);
            return false;
        }
    }

    /**
     * @param $request
     * @param false $deleted
     * @return array
     */
    public function search($request, $deleted = false): array
    {
        $trainingGroup = TrainingGroup::query()->with('schedule.day')->when($deleted, function($q){
            $q->onlyTrashedRelations();
        })->findOrFail($request->input('training_group_id'));

        $assists = $this->model->with( 'inscription.player')->when($deleted, function($q){
            $q->onlyTrashed();
        })->where($request->only(['training_group_id', 'year', 'month']));

        if (!$deleted) {
            $request->merge(['year' => now()->year]);
        }

        return $this->generateTable($assists, $trainingGroup, $request, $deleted);
    }

    /**
     * @param $assists
     * @param $trainingGroup
     * @param $request
     * @param bool $deleted
     * @return array
     */
    private function generateTable($assists, $trainingGroup, $request, bool $deleted = false): array
    {
        $months = config('variables.KEY_MONTHS_INDEX');
        $group_name = $trainingGroup->full_schedule_group;
        $assists = $assists->get();

        $classDays = classDays(
            $request->input('year'),
            array_search($request->input('month'), $months, true),
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

        $urlPrint = route('export.pdf.assists', [
            'training_group_id' => $request->input('training_group_id'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'deleted' => $deleted
        ]);

        $urlPrintExcel = route('export.assists', [
            'training_group_id' => $request->input('training_group_id'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'deleted' => $deleted
        ]);

        return [
            'table' => $table,
            'group_name' => $group_name,
            'count' => $assists->count(),
            'url_print' => $urlPrint,
            'url_print_excel' => $urlPrintExcel
        ];
    }

}
