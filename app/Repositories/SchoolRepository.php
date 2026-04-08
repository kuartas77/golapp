<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\School;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SchoolRepository
{
    use ErrorTrait;
    use UploadFile;

    private School $school;

    public function __construct(School $school)
    {
        $this->school = $school;
    }

    public function getAll()
    {
        $schools = $this->school->query();//->get();
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
            $this->logError("SchoolRepository create", $exception);
        }

        return $school;
    }

    public function schoolsInfo()
    {
        return School::withCount([
            'inscriptions' => fn($q) => $q->where('year', now()->year),
            'players' => fn($q) => $q->whereHas('inscription', fn($q) => $q->where('year', now()->year)),
            'payments' => fn($q) => $q->where('year', now()->year),
            'assists' => fn($q) => $q->where('year', now()->year),
            'skillControls' => fn($q) => $q->whereHas('inscription', fn($q) => $q->where('year', now()->year)),
            'matches' => fn($q) => $q->whereHas('skillsControls', fn($q) => $q->whereHas('inscription', fn($q) => $q->where('year', now()->year))),
            'tournament_payouts' => fn($q) => $q->where('year', now()->year),
            'users',
            'tournaments',
            'trainingGroups',
            'competitionGroups',
            'incidents'
        ]);
    }

    public function checkSchoolCampus(): array
    {
        $school_selected = '';
        $schools = [];
        $isSchool = true;

        if (isAdmin()) {
            $schools = Cache::remember('admin.schools', now()->addMinutes(5), fn() => School::query()->get());
            $school_selected = $this->setSchoolName($schools);
            $schools = $schools->map(fn($item) => ['id'=>$item->id, 'name'=>$item->name]);
            $isSchool = false;
        } elseif (isSchool()) {
            $user = auth()->user();
            $school = getSchool($user);

            if ($multiple = $school->settings->get('MULTIPLE_SCHOOLS')) {
                $campusIds = json_decode($multiple);
                array_push($campusIds, $user->school_id);
                $schools = School::query()->whereIn('id', $campusIds)->get();
                $school_selected = $this->setSchoolName($schools, 'school');
                $schools = $schools->map(fn($item) => ['id'=>$item->id, 'name'=>$item->name]);
            } else {
                $school_selected = $school->name;
            }
        } elseif (isInstructor()) {
            $user = auth()->user();
            $school = getSchool($user);
            $school_selected = $school->name;
        }

        return [
            'is_school' => $isSchool,
            'schools' => $schools,
            'school_selected' => $school_selected,
        ];
    }

    private function setSchoolName($schools, $prefix = 'admin'): string
    {
        $school_selected = '';
        $key = "{$prefix}.selected_school";
        $schoolSelected = request()->session()->get($key);
        $schoolSelected = isset($schoolSelected) ? $schoolSelected : 1;
        if (!is_null($schoolSelected)) {
            $school_selected = $schools->firstWhere('id', $schoolSelected)->name;
        }
        return $school_selected;
    }

    public function chooseSchool(): bool
    {
        $schoolId = request()->input('school_id');
        if (!isset($schoolId)) {
            return false;
        }

        $prefixKey = isAdmin() ? 'admin.' : (isSchool() ? 'school.' : '');
        Session::put("{$prefixKey}selected_school", $schoolId);

        Cache::remember(
            School::KEY_SCHOOL_CACHE . "_{$prefixKey}_{$schoolId}",
            now()->addMinutes(env('SESSION_LIFETIME', 120)),
            fn() => School::with(['settingsValues'])->find($schoolId)
        );
        return true;
    }
}
