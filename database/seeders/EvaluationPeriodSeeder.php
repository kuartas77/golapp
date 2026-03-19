<?php

namespace Database\Seeders;


use App\Models\Evaluations\EvaluationPeriod;
use Illuminate\Database\Seeder;

class EvaluationPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y');

        $periods = [
            [
                'name' => 'Diagnóstico inicial',
                'code' => 'DIAG',
                'year' => $year,
                'starts_at' => "{$year}-01-01",
                'ends_at' => "{$year}-02-15",
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Corte 1',
                'code' => 'T1',
                'year' => $year,
                'starts_at' => "{$year}-03-01",
                'ends_at' => "{$year}-04-30",
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Corte 2',
                'code' => 'T2',
                'year' => $year,
                'starts_at' => "{$year}-05-01",
                'ends_at' => "{$year}-07-31",
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Evaluación final',
                'code' => 'FINAL',
                'year' => $year,
                'starts_at' => "{$year}-10-01",
                'ends_at' => "{$year}-12-15",
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($periods as $period) {
            EvaluationPeriod::updateOrCreate(
                [
                    'year' => $period['year'],
                    'code' => $period['code'],
                    'school_id' => 1
                ],
                $period
            );
        }
    }
}