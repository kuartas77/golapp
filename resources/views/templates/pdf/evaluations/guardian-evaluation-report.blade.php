<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de evaluación</title>
        <link rel="stylesheet" href="{{ public_path('css/dompdf.css') }}">
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #222;
        }

        h1, h2, h3, h4 {
            margin: 0 0 8px 0;
        }

        .text-center {
            text-align: center;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-15 {
            margin-bottom: 15px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .box {
            border: 1px solid #d9d9d9;
            padding: 10px;
            margin-bottom: 12px;
        }

        .section-title {
            background: #f3f3f3;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #d9d9d9;
            margin-bottom: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #d9d9d9;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f3f3f3;
        }

        .small {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="text-center mb-20">
        <h2>{{ $clubName ?? 'Club' }}</h2>
        <h3>Reporte de evaluación del jugador</h3>
    </div>

    <div class="box">
        <table>
            <tr>
                <td width="50%">
                    <strong>Jugador:</strong>
                    {{ $playerName ?? ($evaluation->inscription->player?->full_names ?? $evaluation->inscription->player?->name ?? '—') }}
                </td>
                <td width="50%">
                    <strong>Grupo:</strong>
                    {{ $evaluation->inscription->trainingGroup?->name ?? '—' }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Período:</strong>
                    {{ $evaluation->period?->name ?? '—' }}
                </td>
                <td>
                    <strong>Plantilla:</strong>
                    {{ $evaluation->template?->name ?? '—' }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Tipo:</strong>
                    {{ ucfirst($evaluation->evaluation_type ?? '—') }}
                </td>
                <td>
                    <strong>Estado:</strong>
                    {{ $evaluation->status ?? '—' }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Fecha evaluación:</strong>
                    {{ optional($evaluation->evaluated_at)->format('d/m/Y H:i') ?? '—' }}
                </td>
                <td>
                    <strong>Evaluador:</strong>
                    {{ $evaluation->evaluator?->name ?? '—' }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Nota general:</strong>
                    {{ $evaluation->overall_score !== null ? number_format($evaluation->overall_score, 2) : '—' }}
                </td>
            </tr>
        </table>
    </div>

    <h4 class="section-title">Puntajes por dimensión</h4>
    <table class="mb-15">
        <thead>
            <tr>
                <th>Dimensión</th>
                <th width="150">Puntaje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dimensionScores as $dimension => $score)
                <tr>
                    <td>{{ $dimension }}</td>
                    <td>{{ $score !== null ? number_format((float) $score, 2) : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No hay información por dimensión.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $scoresByDimension = $evaluation->scores
            ->sortBy(fn ($score) => optional($score->criterion)->sort_order ?? 9999)
            ->groupBy(fn ($score) => optional($score->criterion)->dimension ?: 'Sin dimensión');
    @endphp

    @foreach($scoresByDimension as $dimension => $scores)
        <h4 class="section-title">{{ $dimension }}</h4>
        <table class="mb-15">
            <thead>
                <tr>
                    <th>Criterio</th>
                    <th width="90">Puntaje</th>
                    <th width="100">Escala</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scores as $score)
                    <tr>
                        <td>{{ $score->criterion?->name ?? '—' }}</td>
                        <td>{{ $score->score !== null ? number_format($score->score, 2) : '—' }}</td>
                        <td>{{ $score->scale_label ?: $score->scale_value ?: '—' }}</td>
                        <td>{{ $score->comment ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <h4 class="section-title">Conclusiones</h4>
    <div class="box">
        <p><strong>Comentario general:</strong><br>{{ $evaluation->general_comment ?: '—' }}</p>
        <p><strong>Fortalezas:</strong><br>{{ $evaluation->strengths ?: '—' }}</p>
        <p><strong>Oportunidades de mejora:</strong><br>{{ $evaluation->improvement_opportunities ?: '—' }}</p>
        <p class="mb-0"><strong>Recomendaciones:</strong><br>{{ $evaluation->recommendations ?: '—' }}</p>
    </div>
</body>
</html>
