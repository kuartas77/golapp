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
                'score_type' => 'numeric',
            ],
            [
                'code' => 'technical_control',
                'dimension' => 'Técnica',
                'name' => 'Control orientado',
                'weight' => 1.20,
                'sort_order' => 2,
                'score_type' => 'numeric',
            ],
            [
                'code' => 'technical_dribbling',
                'dimension' => 'Técnica',
                'name' => 'Conducción',
                'weight' => 1.00,
                'sort_order' => 3,
                'score_type' => 'numeric',
            ],
            [
                'code' => 'technical_finishing',
                'dimension' => 'Técnica',
                'name' => 'Remate',
                'weight' => 1.10,
                'sort_order' => 4,
                'score_type' => 'numeric',
            ],

            // Táctica
            [
                'code' => 'tactical_positioning',
                'dimension' => 'Táctica',
                'name' => 'Ubicación en campo',
                'weight' => 1.20,
                'sort_order' => 5,
                'score_type' => 'numeric',
            ],
            [
                'code' => 'tactical_game_reading',
                'dimension' => 'Táctica',
                'name' => 'Lectura de juego',
                'weight' => 1.30,
                'sort_order' => 6,
                'score_type' => 'numeric',
            ],
            [
                'code' => 'tactical_decision_making',
                'dimension' => 'Táctica',
                'name' => 'Toma de decisiones',
                'weight' => 1.30,
                'sort_order' => 7,
                'score_type' => 'numeric',
            ],

            // Física
            [
                'code' => 'physical_endurance',
                'dimension' => 'Física',
                'name' => 'Resistencia',
                'weight' => 1.00,
                'sort_order' => 8,
                'score_type' => 'numeric',
            ],
            [
                'code' => 'physical_speed',
                'dimension' => 'Física',
                'name' => 'Velocidad',
                'weight' => 1.10,
                'sort_order' => 9,
                'score_type' => 'numeric',
            ],
            [
                'code' => 'physical_coordination',
                'dimension' => 'Física',
                'name' => 'Coordinación',
                'weight' => 1.00,
                'sort_order' => 10,
                'score_type' => 'numeric',
            ],

            // Actitudinal
            [
                'code' => 'attitudinal_discipline',
                'dimension' => 'Actitudinal',
                'name' => 'Disciplina',
                'weight' => 1.20,
                'sort_order' => 11,
                'score_type' => 'scale',
            ],
            [
                'code' => 'attitudinal_commitment',
                'dimension' => 'Actitudinal',
                'name' => 'Compromiso',
                'weight' => 1.20,
                'sort_order' => 12,
                'score_type' => 'scale',
            ],
            [
                'code' => 'attitudinal_teamwork',
                'dimension' => 'Actitudinal',
                'name' => 'Trabajo en equipo',
                'weight' => 1.10,
                'sort_order' => 13,
                'score_type' => 'scale',
            ],
            [
                'code' => 'attitudinal_punctuality',
                'dimension' => 'Actitudinal',
                'name' => 'Puntualidad',
                'weight' => 0.90,
                'sort_order' => 14,
                'score_type' => 'scale',
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
                    'score_type' => $criterion['score_type'],
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
