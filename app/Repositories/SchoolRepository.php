<?php

namespace App\Repositories;

use App\Models\School;
use App\Traits\ErrorTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolRepository
{
    use ErrorTrait;

    private School $model;

    public function __construct(School $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->query()->get();
    }

    public function create($request): School
    {
        try {
            DB::beginTransaction();

            $this->model->name = $request->name;
            $this->model->agent = $request->agent;
            $this->model->address = $request->address;
            $this->model->phone = $request->phone;
            $this->model->email = $request->email;
            $this->model->is_enable = false;

            if ($request->hasFile('logo'))
            {
                $this->model->logo = $this->saveFileLogo($request, 'logo');
            }
            if ($request->hasFile('logo_min'))
            {
                $this->model->logo_min = $this->saveFileLogo($request, 'logo_min');
            }
            $this->model->save();

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            $this->logError("SchoolRepository create", $exception);
        }

        return $this->model;
    }


    private function saveFileLogo($request, $file)
    {
        $folder = Str::slug($request->name, '_', 'es');
        return $request->file($file)->store("logos/{$folder}");
    }


}
