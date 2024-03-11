<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InscriptionFactory extends Factory
{
    public function definition()
    {
        return [
            'player_id' => '',
            'unique_code' => '',
            'year' => now()->year,
            'training_group_id' => 1,
            'competition_group_id' => 1,
            'start_date' => now()->format('Y-m-d'),
            'category' => categoriesName(now()->subYears(4)->year),
            'photos' => false,
            'scholarship' => false,
            'copy_identification_document' => false,
            'eps_certificate' => false,
            'medic_certificate' => false,
            'study_certificate' => false,
            'overalls' => false,
            'ball' => false,
            'bag' => false,
            'presentation_uniform' => false,
            'competition_uniform' => false,
            'tournament_pay' => false,
            'period_one' => null,
            'period_two' => null,
            'period_three' => null,
            'period_four' => null,
            'school_id' => 1,
        ];
    }

}
