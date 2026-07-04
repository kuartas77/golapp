<?php

namespace App\Service\TrainigSession;

use App\Models\TrainingSession;
use App\Traits\PDFTrait;

class TrainingSessionExportService
{
    use PDFTrait;

    public function __construct(private TrainingSessionAttendanceService $attendanceService) {}

    public function exportSessionPDF(int $id, bool $stream = true)
    {
        $trainingSession = TrainingSession::with(['user', 'training_group', 'tasks'])->schoolId()->where('format', TrainingSession::FORMAT_STANDARD)->findOrFail($id);
        $data = [];
        $data['school'] = getSchool(auth()->user());
        $data['trainingSession'] = $trainingSession;
        $data['tasks'] = $trainingSession->tasks;
        $data['group'] = $trainingSession->training_group;
        if ($trainingSession->attendance_synced_at) {
            $absenceNames = $this->attendanceService->absenceNames($trainingSession);
            $data['resolvedAbsences'] = $absenceNames === [] ? 'Sin ausencias' : implode(', ', $absenceNames);
        } else {
            $data['resolvedAbsences'] = $trainingSession->absences;
        }

        $date = $trainingSession->date;
        $filename = "Sesion de entrenamiento {$trainingSession->training_group->name} {$date}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'training_session.blade.php');
        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    public function exportPlannedSessionPDF(int $id, bool $stream = true)
    {
        $trainingSession = TrainingSession::with(['user', 'training_group', 'phases'])
            ->schoolId()->where('format', TrainingSession::FORMAT_PLANNED)->findOrFail($id);
        $absenceNames = $trainingSession->attendance_synced_at ? $this->attendanceService->absenceNames($trainingSession) : [];
        $data = [
            'school' => getSchool(auth()->user()), 'trainingSession' => $trainingSession,
            'group' => $trainingSession->training_group, 'phases' => $trainingSession->phases,
            'resolvedAbsences' => $trainingSession->attendance_synced_at
                ? ($absenceNames === [] ? 'Sin ausencias' : implode(', ', $absenceNames))
                : $trainingSession->absences,
        ];
        $filename = "Planificacion de sesion {$trainingSession->training_group->name} {$trainingSession->date}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'session_planning.blade.php');
        return $stream ? $this->stream($filename) : $this->output($filename);
    }
}
