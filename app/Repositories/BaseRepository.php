<?php

declare(strict_types=1);

namespace App\Repositories;


use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected \Illuminate\Database\Eloquent\Model $model;

    protected array $relations;

    public function __construct(Model $model, array $relations = [])
    {
        $this->model = $model;
        $this->relations = $relations;
    }

    public function all()
    {
        $query = $this->model;

        if ($this->relations !== []) {
            $query = $query->with($this->relations);
        }

        return $query->get();
    }

    public function get($id)
    {
        return $this->model->find($id);
    }

    public function save(Model $model): Model
    {
        $model->save();

        return $model;
    }

    public function delete(Model $model): Model
    {
        $model->delete();

        return $model;
    }

}
