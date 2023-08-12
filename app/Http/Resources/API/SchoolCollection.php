<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SchoolCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'email' => $school->email,
                'created_at' => $school->created_at,
                'address' => $school->address,
                'agent' => $school->agent,
                'assists_count' => $school->assists_count,
                'competition_groups_count' => $school->competition_groups_count,
                'incidents_count' => $school->incidents_count,
                'inscriptions_count' => $school->inscriptions_count,
                'matches_count' => $school->matches_count,
                'payments_count' => $school->payments_count,
                'phone' => $school->phone,
                'players_count' => $school->players_count,
                'skill_controls_count' => $school->skill_controls_count,
                'slug' => $school->slug,
                'tournaments_count' => $school->tournaments_count,
                'training_groups_count' => $school->training_groups_count,
                'is_enable' => $school->is_enable,
                'deleted_at' => $school->deleted_at,
                'logo' => $school->logo_file,
            ];
        })->toArray();
    }
}
