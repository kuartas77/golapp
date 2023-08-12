<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\RegisterNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;

class SchoolRepository
{
    use ErrorTrait;
    use UploadFile;

    private School $model;

    public function __construct(School $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        $schools = $this->model->query()->get();
        $schools->setAppends(['url_edit','url_update', 'url_show', 'url_destroy','logo_file']);
        return $schools;
    }

    public function update(FormRequest $request, School $school): School
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['logo'] = $this->saveFile($request, 'logo');

            $school->update($data);

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("SchoolRepository create", $exception);
        }

        return $school;
    }

    public function schoolsInfo(int $school_id = null)
    {
        $query = School::withCount([
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
        $response = new Collection();
        if($school_id){
            $response = $query->where('id', $school_id)->first();
        }else{
            $response = $query->get();
        }

        return $response;
    }
}
