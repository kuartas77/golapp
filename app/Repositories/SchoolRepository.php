<?php

namespace App\Repositories;

use App\Models\School;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;
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
        return $this->model->query()->get();
    }

    public function create(FormRequest $request): School
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['logo'] = $this->saveFile($request, 'logo');

            $this->model->create($data);

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("SchoolRepository create", $exception);
        }

        return $this->model;
    }
}
