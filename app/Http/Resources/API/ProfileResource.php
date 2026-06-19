<?php

declare(strict_types=1);

namespace App\Http\Resources\API;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ProfileResource extends JsonResource
{
    public function __construct($resource, private readonly bool $canUpdate = false)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $profile = $this->resource;
        $user = $profile->user;

        return [
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
            'profile' => [
                'id' => $profile->id,
                'date_birth' => $profile->date_birth ? (string) $profile->date_birth : null,
                'identification_document' => $profile->identification_document,
                'gender' => $profile->gender,
                'address' => $profile->address,
                'phone' => $profile->phone,
                'mobile' => $profile->mobile,
                'studies' => $profile->studies,
                'references' => $profile->references,
                'contacts' => $profile->contacts,
                'experience' => $profile->experience,
                'position' => $profile->position,
                'aptitude' => $profile->aptitude,
            ],
            'can_update' => $this->canUpdate,
            'gender_options' => $this->options(config('variables.KEY_GENDERS', [])),
            'position_options' => $this->options(config('variables.KEY_POSITIONS_ASSIGNED', [])),
        ];
    }

    private function options(array $options): array
    {
        return collect($options)
            ->map(fn ($label, $value) => [
                'value' => (string) $value,
                'label' => (string) $label,
            ])
            ->values()
            ->all();
    }
}
