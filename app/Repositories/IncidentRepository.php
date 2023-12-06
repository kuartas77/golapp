<?php


namespace App\Repositories;


use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncidentRepository extends BaseRepository
{

    public function __construct(Incident $incident)
    {
        parent::__construct($incident, ['professor']);
    }

    public function all()
    {
        return $this->model->query()->with($this->relations)
            ->groupBy('user_incident_id', 'slug_name')
            ->select('user_incident_id', 'slug_name', DB::raw('count(*) as count'))
            ->get();
    }

    public function get($id)
    {
        return $this->model->query()->with($this->relations)
            ->where('slug_name', $id)
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * @param $request
     * @return Incident
     */
    public function createIncident($request): Model
    {
        $professor = User::query()->find($request->input('user_incident_id'));
        $arguments = $request->validated();
        $arguments['user_created_id'] = auth()->id();
        $arguments['slug_name'] = Str::slug($professor->name);
        return Incident::query()->create($arguments);
    }
}
