<?php

namespace App\Service\TrainigSession;

use App\Models\TrainingSession;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;

class TrainingSessionExportService
{
    use PDFTrait;
    use ErrorTrait;

    public function exportSessionPDF(int $id, bool $stream = true)
    {
        $trainingSession = TrainingSession::with(['user', 'training_group', 'tasks'])->schoolId()->findOrFail($id);
        $data = [];
        $data['school'] = getSchool(auth()->user());
        $data['trainingSession'] = $trainingSession;
        $data['tasks'] = $trainingSession->tasks;
        $data['group'] = $trainingSession->training_group;

        $date = $trainingSession->date;
        $filename = "Sesion de entrenamiento {$trainingSession->training_group->name} {$date}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'training_session.blade.php');
        return $stream ? $this->stream($filename) : $this->output($filename);
    }
}
