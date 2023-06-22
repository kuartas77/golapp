<?php

namespace App\Http\Resources\API\Inscriptions;

use App\Models\Inscription;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

class InscriptionResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $resource = Inscription::class;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'training_group_id' => $this->training_group_id,
            'inscription_id' => $this->inscription_id,
            'year' => $this->year,
            'month' => $this->month,
            'assistance_one' => $this->assistance_one,
            'assistance_two' => $this->assistance_two,
            'assistance_three' => $this->assistance_three,
            'assistance_four' => $this->assistance_four,
            'assistance_five' => $this->assistance_five,
            'assistance_six' => $this->assistance_six,
            'assistance_seven' => $this->assistance_seven,
            'assistance_eight' => $this->assistance_eight,
            'assistance_nine' => $this->assistance_nine,
            'assistance_ten' => $this->assistance_ten,
            'assistance_eleven' => $this->assistance_eleven,
            'assistance_twelve' => $this->assistance_twelve,
            'assistance_thirteen' => $this->assistance_thirteen,
            'assistance_fourteen' => $this->assistance_fourteen,
            'assistance_fifteen' => $this->assistance_fifteen,
            'assistance_sixteen' => $this->assistance_sixteen,
            'assistance_seventeen' => $this->assistance_seventeen,
            'assistance_eighteen' => $this->assistance_eighteen,
            'assistance_nineteen' => $this->assistance_nineteen,
            'assistance_twenty' => $this->assistance_twenty,
            'assistance_twenty_one' => $this->assistance_twenty_one,
            'assistance_twenty_two' => $this->assistance_twenty_two,
            'assistance_twenty_three' => $this->assistance_twenty_three,
            'assistance_twenty_four' => $this->assistance_twenty_four,
            'assistance_twenty_five' => $this->assistance_twenty_five,
            'observations' => $this->observations,
        ];
    }
}
