<?php

declare(strict_types=1);

namespace App\Http\Resources\API\Portal;

use App\Models\Inscription;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianPlayerListResource extends JsonResource
{
    public static $wrap = null;

    /** @var Player */
    public $resource;

    public function toArray(Request $request): array
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->relationLoaded('inscriptions')
            ? $this->inscriptions->first()
            : ($this->relationLoaded('inscription') ? $this->inscription : null);
        $trainingGroup = $inscription?->relationLoaded('trainingGroup') ? $inscription->trainingGroup : null;

        return [
            'id' => $this->id,
            'unique_code' => $this->unique_code,
            'full_names' => $this->full_names,
            'photo_url' => $this->photo_url,
            'category' => $this->category,
            'school' => $this->whenLoaded('schoolData', fn () => [
                'id' => $this->schoolData->id,
                'name' => $this->schoolData->name,
                'slug' => $this->schoolData->slug,
                'logo_file' => $this->schoolData->logo_file,
            ]),
            'current_inscription' => $inscription ? [
                'id' => $inscription->id,
                'year' => $inscription->year,
                'training_group' => $trainingGroup ? [
                    'id' => $trainingGroup->id,
                    'name' => $trainingGroup->name,
                ] : null,
            ] : null,
        ];
    }
}
