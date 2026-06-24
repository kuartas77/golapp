<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\People;
use Illuminate\Support\Collection;

class PeopleRepository
{

    private People $people;

    public function __construct(People $people)
    {
        $this->people = $people;
    }

    /**
     * @param $people
     */
    public function getPeopleIds($people): Collection
    {
        $peopleIds = collect();
        foreach ($people as $person) {
            if ($person['relationship'] != '' && $person['names'] != '' && $person['identification_card'] != '') {
                $peopleIds->push(optional($this->createOrUpdatePeople($person))->id);
            }
        }

        return $peopleIds;
    }

    public function createOrUpdatePeople($person): People
    {
        $person['relationship'] = $this->normalizeRelationship($person['relationship']);

        return $this->people->query()->updateOrCreate(
            [
                'identification_card' => $person['identification_card']
            ],
            [
                'names' => $person['names'],
                'tutor' => $person['tutor'],
                'relationship' => $person['relationship'],
                'phone' => ($person['phone'] ?? null),
                'document_expedition_place' => ($person['document_expedition_place'] ?? null),
                'email' => ($person['email'] ?? null),
                'mobile' => ($person['mobile'] ?? null),
                'profession' => ($person['profession'] ?? null),
                'business' => ($person['business'] ?? null),
                'position' => ($person['position'] ?? null),
            ]
        );
    }

    private function normalizeRelationship($relationship): string
    {
        $relationships = config('variables.KEY_RELATIONSHIPS_SELECT', []);

        if (array_key_exists($relationship, $relationships)) {
            return (string) $relationship;
        }

        $normalizedRelationship = mb_strtoupper(trim((string) $relationship));
        $relationshipId = array_search($normalizedRelationship, $relationships, true);

        return $relationshipId === false ? (string) $relationship : (string) $relationshipId;
    }
}
