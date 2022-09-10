<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\RegisterNotification;
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

        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("SchoolRepository create", $exception);
        }

        return $school;
    }
}
