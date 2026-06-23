<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\School;
use App\Service\School\CurrentSchoolContext;
use App\Traits\UploadFile;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class SchoolRepository
{
    use UploadFile;

    private School $school;

    public function __construct(School $school, private CurrentSchoolContext $schoolContext)
    {
        $this->school = $school;
    }

    public function getAll()
    {
        $schools = $this->school->query(); // ->get();

        // $schools->setAppends(['url_edit', 'url_update', 'url_show', 'url_destroy', 'logo_file']);
        return $schools;
    }

    public function update(FormRequest $formRequest, School $school): School
    {
        try {
            DB::beginTransaction();

            $data = $formRequest->validated();
            $data['logo'] = $this->saveFile($formRequest, 'logo');

            $school->update($data);

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            report($exception);
        }

        return $school;
    }

    public function schoolsInfo()
    {
        return School::withCount([
            'inscriptions' => fn ($q) => $q->where('year', now()->year),
            'players' => fn ($q) => $q->whereHas('inscription', fn ($q) => $q->where('year', now()->year)),
            'payments' => fn ($q) => $q->where('year', now()->year),
            'assists' => fn ($q) => $q->where('year', now()->year),
            'skillControls' => fn ($q) => $q->whereHas('inscription', fn ($q) => $q->where('year', now()->year)),
            'matches' => fn ($q) => $q->whereHas('skillsControls', fn ($q) => $q->whereHas('inscription', fn ($q) => $q->where('year', now()->year))),
            'tournament_payouts' => fn ($q) => $q->where('year', now()->year),
            'users',
            'tournaments',
            'trainingGroups',
            'competitionGroups',
            'incidents',
        ]);
    }

    public function checkSchoolCampus(): array
    {
        $user = auth()->user();
        $school = $this->schoolContext->current($user);
        $allowedSchools = $this->schoolContext->allowedSchools($user);
        $canSwitch = isAdmin() || $allowedSchools->count() > 1;
        $schools = $canSwitch
            ? $allowedSchools->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])->values()
            : collect();

        return [
            'is_school' => ! isAdmin(),
            'schools' => $schools,
            'school_selected' => $school->name,
            'current_school_id' => $school->id,
        ];
    }

    public function chooseSchool(): bool
    {
        return $this->schoolContext->select((int) request()->input('school_id'), auth()->user());
    }
}
