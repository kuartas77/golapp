<?php


namespace App\Repositories;

use App\Models\People;
use Illuminate\Support\Collection;

class PeopleRepository
{

    /**
     * @var People
     */
    private People $model;

    public function __construct(People $model)
    {
        $this->model = $model;
    }

    /**
     * @param $people
     * @return Collection
     */
    public function getPeopleIds($people): Collection
    {
        $relationship = config('variables.KEY_RELATIONSHIPS_SELECT');
        $peopleIds = collect();
        foreach ($people as $person) {
            if ($person['relationship'] != '' && $person['names'] != '' && $person['identification_card'] != '') {
                $person['tutor'] = isset($person['tutor']);
                $person['relationship_name'] = $relationship[$person['relationship']];
                $peopleIds->push(optional($this->createOrUpdatePeople($person))->id);
            }
        }
        return $peopleIds;
    }

    public function createOrUpdatePeople($person): People
    {
        return $this->model->query()->updateOrCreate(
            [
                'names' => $person['names'],
                'identification_card' => $person['identification_card']
            ],
            [
                'tutor' => $person['tutor'],
                'relationship' => $person['relationship'],
                'phone' => ($person['phone'] ?? null),
                'email' => ($person['email'] ?? null),
                'mobile' => ($person['mobile'] ?? null),
                'profession' => ($person['profession'] ?? null),
                'business' => ($person['business'] ?? null),
                'position' => ($person['position'] ?? null),
            ]
        );
    }
}
