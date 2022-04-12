<?php

namespace App\Http\Resources\API;

use App\Models\School;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'url_activate' => $user->url_activate,
                'school' => $this->formatSchool($user->school),
                'roles' => $this->formatRoles($user->roles)
            ];
        })->toArray();
    }

    private function formatSchool(School $school)
    {
        return [
            'id' => $school->id,
            'name' => $school->name,
            'slug' => $school->slug,
        ];
    }

    private function formatRoles(Collection $roles)
    {
        return $roles->map(fn ($role) => ['id' => $role->id, 'name' => $role->name]);
    }
}
