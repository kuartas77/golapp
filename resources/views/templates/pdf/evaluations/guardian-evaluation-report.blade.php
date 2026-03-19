<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Evaluación</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .subtitle {
            font-size: 11px;
            color: #555;
        }

        .section {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
        }

        .grid td,
        .grid th {
            border: 1px solid #dcdcdc;
            padding: 6px;
            vertical-align: top;
        }

        .grid th {
            background: #f2f2f2;
            text-align: left;
        }

        .summary-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        .score-big {
            font-size: 22px;
            font-weight: bold;
        }

        .muted {
            color: #666;
        }

        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">{{ $clubName }}</div>
        <div class="subtitle">Reporte de evaluación para acudiente</div>
    </div>

    <div class="section">
        <div class="section-title">Datos generales</div>

        <table class="grid">
            <tr>
                <th width="25%">Jugador</th>
                <td width="25%">{{ $playerName }}</td>
                <th width="25%">Grupo</th>
                <td width="25%">{{ $evaluation->inscription->trainingGroup?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Período</th>
                <td>{{ $evaluation->period?->name ?? 'N/A' }}</td>
                <th>Fecha de evaluación</th>
                <td>{{ optional($evaluation->evaluated_at)->format('Y-m-d H:i') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Plantilla</th>
                <td>{{ $evaluation->template?->name ?? 'N/A' }}</td>
                <th>Evaluador</th>
                <td>{{ $evaluation->evaluator?->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Resultado general</div>

        <div class="summary-box">
            <div class="muted">Puntuación general</div>
            <div class="score-big">
                {{ number_format((float) ($evaluation->overall_score ?? 0), 2) }}
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Promedio por dimensión</div>

        <table class="grid">
            <thead>
                <tr>
                    <th>Dimensión</th>
                    <th width="25%">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dimensionScores as $dimension => $score)
                    <tr>
                        <td>{{ $dimension }}</td>
                        <td>{{ number_format((float) $score, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No hay datos de dimensiones disponibles.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalle por criterio</div>

        <table class="grid">
            <thead>
                <tr>
                    <th width="20%">Dimensión</th>
                    <th width="28%">Criterio</th>
                    <th width="12%">Puntaje</th>
                    <th width="40%">Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evaluation->scores->sortBy(fn($score) => ($score->criterion->sort_order ?? 9999)) as $score)
                    <tr>
                        <td>{{ $score->criterion?->dimension ?? 'N/A' }}</td>
                        <td>{{ $score->criterion?->name ?? 'N/A' }}</td>
                        <td>{{ $score->score !== null ? number_format((float) $score->score, 2) : 'N/A' }}</td>
                        <td>{{ $score->comment ?? 'Sin observación' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Comentarios del entrenador</div>

        <table class="grid">
            <tr>
                <th width="28%">Comentario general</th>
                <td>{{ $evaluation->general_comment ?: 'Sin comentario general.' }}</td>
            </tr>
            <tr>
                <th>Fortalezas</th>
                <td>{{ $evaluation->strengths ?: 'No registradas.' }}</td>
            </tr>
            <tr>
                <th>Oportunidades de mejora</th>
                <td>{{ $evaluation->improvement_opportunities ?: 'No registradas.' }}</td>
            </tr>
            <tr>
                <th>Recomendaciones</th>
                <td>{{ $evaluation->recommendations ?: 'No registradas.' }}</td>
            </tr>
        </table>
    </div>

    <div class="section small muted">
        Documento generado automáticamente por {{ $clubName }} el {{ now()->format('Y-m-d H:i') }}.
    </div>

</body>
</html>