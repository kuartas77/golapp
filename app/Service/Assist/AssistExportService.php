<?php

namespace App\Service\Assist;

use App\Models\Assist;
use App\Models\TrainingGroup;
use App\Traits\ErrorTrait;
use App\Traits\PDFTrait;

class AssistExportService
{
    use PDFTrait;
    use ErrorTrait;

    /**
     * @param $params
     * @param $deleted
     * @return mixed
     * @throws MpdfException
     */
    public function generatePDF($params, $deleted)
    {
        list($assists, $classDays, $group_name, $group, $school) = $this->dataExport($params, $deleted);
        $school->logo_local = $school->logo_local;
        $data['school'] = $school;
        $data['assists'] = $assists;
        $data['count'] = $assists->count() + 1;
        $data['result'] = (40 - $data['count']);
        $data['classDays'] = $classDays;
        $group->instructors_names = $group->instructors_names;
        $data['group'] = $group;
        $data['group_name'] = $group_name;
        $data['month'] = config('variables.KEY_MONTHS_INDEX')[$params['month']];
        $data['year'] = $params['year'];
        $data['optionAssist'] = config('variables.KEY_ASSIST_LETTER');
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'assists.blade.php');

        return $this->stream("{$group_name}.pdf");
    }

    /**
     * @param $params
     * @param false $deleted
     * @return array
     */
    public function dataExport($params, bool $deleted = false): array
    {
        if ($deleted) {
            $group = TrainingGroup::query()->onlyTrashedRelations()->schoolId()->find($params['training_group_id']);

            $assists = Assist::query()->onlyTrashedRelations()->schoolId()->where([
                'training_group_id' => $params['training_group_id'],
                'month' => $params['month'],
                'year' => $params['year']
            ])->get();
        } else {
            $group = TrainingGroup::query()->schoolId()->with('instructors')
                ->find($params['training_group_id']);

            $assists = Assist::query()->schoolId()->with(['inscription.player'])->where([
                'training_group_id' => $params['training_group_id'],
                'month' => $params['month'], 'year' => $params['year']
            ])->get();
        }

        $classDays = classDays(
            $params['year'],
            $params['month'],
            array_map('dayToNumber', $group->explode_days)
        );

        $school = getSchool(auth()->user());

        return [$assists, $classDays, $group->full_schedule_group, $group, $school];
    }
}
