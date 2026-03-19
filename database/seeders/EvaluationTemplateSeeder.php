<?php

namespace Database\Seeders;

use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\EvaluationTemplateCriterion;
use Illuminate\Database\Seeder;

class EvaluationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y');

        $template = EvaluationTemplate::updateOrCreate(
            [
                'name' => 'Plantilla Base Jugadores de Campo',
                'year' => $year,
                'version' => 1,
                'school_id' => 1
            ],
            [
                'description' => 'Plantilla base para evaluaciones periódicas de jugadores de campo.',
                'training_group_id' => null,
                'status' => 'active',
                'created_by' => null,
            ]
        );

        $criteria = [
            // Técnica
            [
                'code' => 'technical_pass',
                'dimension' => 'Técnica',
                'name' => 'Pase',
                'weight' => 1.20,
                'sort_order' => 1,
            ],
            [
                'code' => 'technical_control',
                'dimension' => 'Técnica',
                'name' => 'Control orientado',
                'weight' => 1.20,
                'sort_order' => 2,
            ],
            [
                'code' => 'technical_dribbling',
                'dimension' => 'Técnica',
                'name' => 'Conducción',
                'weight' => 1.00,
                'sort_order' => 3,
            ],
            [
                'code' => 'technical_finishing',
                'dimension' => 'Técnica',
                'name' => 'Remate',
                'weight' => 1.10,
                'sort_order' => 4,
            ],

            // Táctica
            [
                'code' => 'tactical_positioning',
                'dimension' => 'Táctica',
                'name' => 'Ubicación en campo',
                'weight' => 1.20,
                'sort_order' => 5,
            ],
            [
                'code' => 'tactical_game_reading',
                'dimension' => 'Táctica',
                'name' => 'Lectura de juego',
                'weight' => 1.30,
                'sort_order' => 6,
            ],
            [
                'code' => 'tactical_decision_making',
                'dimension' => 'Táctica',
                'name' => 'Toma de decisiones',
                'weight' => 1.30,
                'sort_order' => 7,
            ],

            // Física
            [
                'code' => 'physical_endurance',
                'dimension' => 'Física',
                'name' => 'Resistencia',
                'weight' => 1.00,
                'sort_order' => 8,
            ],
            [
                'code' => 'physical_speed',
                'dimension' => 'Física',
                'name' => 'Velocidad',
                'weight' => 1.10,
                'sort_order' => 9,
            ],
            [
                'code' => 'physical_coordination',
                'dimension' => 'Física',
                'name' => 'Coordinación',
                'weight' => 1.00,
                'sort_order' => 10,
            ],

            // Actitudinal
            [
                'code' => 'attitudinal_discipline',
                'dimension' => 'Actitudinal',
                'name' => 'Disciplina',
                'weight' => 1.20,
                'sort_order' => 11,
            ],
            [
                'code' => 'attitudinal_commitment',
                'dimension' => 'Actitudinal',
                'name' => 'Compromiso',
                'weight' => 1.20,
                'sort_order' => 12,
            ],
            [
                'code' => 'attitudinal_teamwork',
                'dimension' => 'Actitudinal',
                'name' => 'Trabajo en equipo',
                'weight' => 1.10,
                'sort_order' => 13,
            ],
            [
                'code' => 'attitudinal_punctuality',
                'dimension' => 'Actitudinal',
                'name' => 'Puntualidad',
                'weight' => 0.90,
                'sort_order' => 14,
            ],
        ];

        foreach ($criteria as $criterion) {
            EvaluationTemplateCriterion::updateOrCreate(
                [
                    'evaluation_template_id' => $template->id,
                    'code' => $criterion['code'],
                ],
                [
                    'dimension' => $criterion['dimension'],
                    'name' => $criterion['name'],
                    'description' => null,
                    'score_type' => 'numeric',
                    'min_score' => 1,
                    'max_score' => 5,
                    'weight' => $criterion['weight'],
                    'sort_order' => $criterion['sort_order'],
                    'is_required' => true,
                ]
            );
        }
    }
}
