<?php

declare(strict_types=1);

namespace App\Service\Methodology;

use App\Models\MethodologyRecord;
use App\Traits\PDFTrait;
use Illuminate\Support\Str;

class MethodologyRecordExportService
{
    use PDFTrait;

    public function export(MethodologyRecord $record, bool $stream = true): mixed
    {
        $record->loadMissing(['school', 'user.profile', 'trainingGroup:id,name,category']);

        $data = [
            'school' => getSchool(auth()->user()),
            'record' => $record,
            'title' => $this->typeLabel($record->type),
            'fieldGroups' => $this->fieldGroups($record->type),
            'planningPhases' => $this->planningPhases(),
        ];

        $this->setConfigurationMpdf([
            'format' => 'A4',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 6,
            'margin_bottom' => 7,
        ]);

        $this->createPDF($data, 'methodology/record.blade.php');

        $filename = Str::slug($this->typeLabel($record->type) . ' ' . $record->title) . '.pdf';

        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    public function typeLabel(string $type): string
    {
        return match ($type) {
            MethodologyRecord::TYPE_CHARACTERIZATION_SHEET => 'Ficha técnica de caracterización',
            MethodologyRecord::TYPE_MONTHLY_REPORT => 'Informe mensual',
            MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT => 'Informe mensual categoría',
            default => 'Formato de planificación',
        };
    }

    public function fieldGroups(string $type): array
    {
        return match ($type) {
            MethodologyRecord::TYPE_CHARACTERIZATION_SHEET => [
                [
                    'title' => 'Ficha técnica de caracterización',
                    'fields' => [
                        ['key' => 'category', 'label' => 'Categoría'],
                        ['key' => 'year_semester', 'label' => 'Año-semestre'],
                        ['key' => 'age_group', 'label' => 'Grupo etario'],
                        ['key' => 'competitions', 'label' => 'Competencias 2026'],
                        ['key' => 'sport_objectives', 'label' => 'Objetivos deportivos 2026 (entrenador)'],
                        ['key' => 'formative_objectives', 'label' => 'Objetivos formativos de la categoría año 2026'],
                        ['key' => 'constitutive_values', 'label' => 'Valores constitutivos de la categoría'],
                        ['key' => 'tactical_schemes', 'label' => 'Esquemas tácticos habituales'],
                        ['key' => 'game_model', 'label' => 'Modelo de juego'],
                        ['key' => 'offensive_defensive_principles', 'label' => 'Principios ofensivos y defensivos trabajados'],
                        ['key' => 'priority_technical_elements', 'label' => 'Elementos técnicos prioritarios'],
                        ['key' => 'internal_rules', 'label' => 'Reglamento interno de la categoría'],
                    ],
                ],
            ],
            MethodologyRecord::TYPE_MONTHLY_REPORT => [
                [
                    'title' => 'Informe mensual',
                    'fields' => [
                        ['key' => 'coach', 'label' => 'Entrenador'],
                        ['key' => 'category', 'label' => 'Categoría'],
                        ['key' => 'report_month', 'label' => 'Mes correspondiente al informe'],
                    ],
                ],
            ],
            MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT => [
                [
                    'title' => 'Informe mensual categoría',
                    'fields' => [
                        ['key' => 'coach', 'label' => 'Entrenador'],
                        ['key' => 'category', 'label' => 'Categoría'],
                        ['key' => 'report_month', 'label' => 'Mes correspondiente al informe'],
                        ['key' => 'monthly_objectives_description', 'label' => 'Objetivos planteados en el mes en curso.'],
                        ['key' => 'monthly_achievements_description', 'label' => 'Logros obtenidos en el mes en curso.'],
                        ['key' => 'monthly_difficulties_description', 'label' => 'Dificultades presentadas en el mes en curso.'],
                        ['key' => 'sport_values_description', 'label' => 'Valores deportivos abordados'],
                        ['key' => 'specific_player_news_description', 'label' => 'Situaciones o novedades específicas con jugadores. (enfermedad, incapacidad, lesión, evolución deportiva o entre otras).'],
                        ['key' => 'player_follow_up_description', 'label' => 'Seguimiento y/o control que se llevó o se está llevando a cabo con el jugador.'],
                    ],
                ],
            ],
            default => [
                [
                    'title' => 'Encabezado',
                    'fields' => [
                        ['key' => 'category', 'label' => 'Categoría'],
                        ['key' => 'coach', 'label' => 'Entrenador'],
                        ['key' => 'session', 'label' => 'Sesión'],
                        ['key' => 'objective', 'label' => 'Objetivo'],
                    ],
                ],
                [
                    'title' => 'Estructuras preferentes',
                    'fields' => [
                        ['key' => 'coordinative', 'label' => 'Coordinativa'],
                        ['key' => 'cognitive', 'label' => 'Cognitiva'],
                        ['key' => 'conditional', 'label' => 'Condicional'],
                        ['key' => 'emotional_volitional', 'label' => 'Emotivo-volitiva'],
                    ],
                ],
                [
                    'title' => 'Cierre',
                    'fields' => [
                        ['key' => 'material', 'label' => 'Material'],
                        ['key' => 'observations', 'label' => 'Observaciones'],
                    ],
                ],
            ],
        };
    }

    public function planningPhases(): array
    {
        return [
            [
                'key' => 'initial_phase',
                'label' => 'Fase inicial',
                'time' => 'initial_phase_time',
                'dosage' => 'initial_phase_dosage',
                'description' => 'initial_phase_description',
            ],
            [
                'key' => 'central_phase_one',
                'label' => 'Fase central 1',
                'time' => 'central_phase_one_time',
                'dosage' => 'central_phase_one_dosage',
                'description' => 'central_phase_one_description',
            ],
            [
                'key' => 'central_phase_two',
                'label' => 'Fase central 2',
                'time' => 'central_phase_two_time',
                'dosage' => 'central_phase_two_dosage',
                'description' => 'central_phase_two_description',
            ],
            [
                'key' => 'central_phase_three',
                'label' => 'Fase central 3',
                'time' => 'central_phase_three_time',
                'dosage' => 'central_phase_three_dosage',
                'description' => 'central_phase_three_description',
            ],
        ];
    }
}
