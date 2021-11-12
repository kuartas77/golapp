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

    public function createOrUpdatePeople($person):People
    {
        return $this->model->updateOrCreate(
            [
                'names' => $person['names'],
                'identification_card' => $person['identification_card']
            ],
            [
                'is_tutor' => $person['tutor'],
                'relationship' => $person['relationship'],
                'phone' => $person['phone'],
                'mobile' => $person['mobile'],
                'profession' => $person['profession'],
                'business' => $person['business'],
                'position' => $person['position'],
                'relationship_name' => $person['relationship_name']
            ]
        );
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
            $person['tutor'] = isset($person['tutor']);
            $person['relationship_name'] = $relationship[$person['relationship']];
            $peopleIds->push(optional($this->createOrUpdatePeople($person))->id);
        }
        return $peopleIds;
    }
}
