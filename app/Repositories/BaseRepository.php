<?php


namespace App\Repositories;


use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected $model;
    protected $relations;

    public function __construct(Model $model, array $relations = [])
    {
        $this->model = $model;
        $this->relations = $relations;
    }

    public function all()
    {
        $query = $this->model;

        if(!empty($this->relations)) {
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
