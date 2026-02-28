<?php

namespace App\Http\Resources\API\Players;

use App\Models\Player;
use App\Service\Notification\TopicService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PlayerResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $resource = Player::class;

    public function __construct($resource, public $loadRelations = false)
    {
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $response = [
            'id' => $this->id,
            'unique_code' => $this->unique_code,
            'names' => $this->names,
            'last_names' => $this->last_names,
            'category' => $this->category,
            'full_names' => $this->full_names,
            'photo_url' => str_replace('img/dynamic', 'api/img/dynamic', $this->photo_url),
            'group_id' => $this->when($request->has('group_id'), $request->group_id),
            'inscription_id' => $this->inscription_id
        ];

        if($this->loadRelations) {

            $team = $this->when(
                $this->relationLoaded('inscription') && $this->inscription->relationLoaded('trainingGroup'),
                fn () => $this->inscription->trainingGroup->name
            );

            $response['inscription_id'] = $this->whenLoaded('inscription',$this->inscription->id);
            $response['school_id'] = $this->whenLoaded('schoolData',"{$this->school_id}");
            $response['school_name'] = $this->whenLoaded('schoolData',$this->schoolData->name);
            $response['school_slug'] = $this->whenLoaded('schoolData',$this->schoolData->slug);
            $response['school_logo'] = $this->whenLoaded('schoolData',$this->schoolData->logo_file);
            $response['team'] = $team;
            $response['topics'] = TopicService::generatePlayerTopics($this->resource->resource);
        }

        return $response;
    }
}
