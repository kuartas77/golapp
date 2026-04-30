<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comparativo de evaluaciones</title>
        <link rel="stylesheet" href="{{ public_path('css/dompdf.css') }}">
        <link rel="stylesheet" href="{{ public_path('css/dompdf-overrides.css') }}">
    <style>
        body {
            font-family: sans-serif;
            font-size: 10.5px;
            color: #222;
        }

        h1, h2, h3, h4 {
            margin: 0 0 8px 0;
        }

        .text-center {
            text-align: center;
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
            border: 1px solid #000000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f3f3f3;
        }
    </style>
</head>
<body>
    <div class="text-center mb-20">
        <h2>{{ $clubName ?? 'Club' }}</h2>
        <h3>Comparativo de evaluaciones del jugador</h3>
    </div>

    <div class="box">
        <table>
            <tr>
                <td width="50%">
                    <strong>Jugador:</strong> {{ $playerName ?? data_get($comparison, 'player.name', '—') }}
                </td>
                <td width="50%">
                    <strong>Grupo:</strong> {{ data_get($comparison, 'inscription.training_group_name', '—') }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Período A:</strong> {{ data_get($comparison, 'period_a.period_name', '—') }}
                </td>
                <td>
                    <strong>Período B:</strong> {{ data_get($comparison, 'period_b.period_name', '—') }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Plantilla A:</strong> {{ data_get($comparison, 'period_a.template_name', '—') }}
                </td>
                <td>
                    <strong>Plantilla B:</strong> {{ data_get($comparison, 'period_b.template_name', '—') }}
                </td>
            </tr>
        </table>
    </div>

    <h4 class="section-title">Resultado general</h4>
    <table class="mb-15">
        <thead>
            <tr>
                <th>Período A</th>
                <th>Período B</th>
                <th>Delta</th>
                <th>Tendencia</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ data_get($comparison, 'overall.period_a_score') !== null ? number_format((float) data_get($comparison, 'overall.period_a_score'), 2) : '—' }}
                </td>
                <td>
                    {{ data_get($comparison, 'overall.period_b_score') !== null ? number_format((float) data_get($comparison, 'overall.period_b_score'), 2) : '—' }}
                </td>
                <td>
                    {{ data_get($comparison, 'overall.delta') !== null ? number_format((float) data_get($comparison, 'overall.delta'), 2) : '—' }}
                </td>
                <td>
                    {{ data_get($comparison, 'overall.trend', 'neutral') }}
                </td>
            </tr>
        </tbody>
    </table>

    <h4 class="section-title">Comparativo por dimensión</h4>
    <table class="mb-15">
        <thead>
            <tr>
                <th>Dimensión</th>
                <th>Período A</th>
                <th>Período B</th>
                <th>Delta</th>
                <th>Tendencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse(data_get($comparison, 'dimensions', []) as $item)
                <tr>
                    <td>{{ data_get($item, 'dimension', '—') }}</td>
                    <td>{{ data_get($item, 'period_a_score') !== null ? number_format((float) data_get($item, 'period_a_score'), 2) : '—' }}</td>
                    <td>{{ data_get($item, 'period_b_score') !== null ? number_format((float) data_get($item, 'period_b_score'), 2) : '—' }}</td>
                    <td>{{ data_get($item, 'delta') !== null ? number_format((float) data_get($item, 'delta'), 2) : '—' }}</td>
                    <td>{{ data_get($item, 'trend', 'neutral') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay información por dimensión.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h4 class="section-title">Comparativo por criterio</h4>
    <table class="mb-15">
        <thead>
            <tr>
                <th>Dimensión</th>
                <th>Criterio</th>
                <th>Período A</th>
                <th>Período B</th>
                <th>Delta</th>
                <th>Comentario A</th>
                <th>Comentario B</th>
            </tr>
        </thead>
        <tbody>
            @forelse(data_get($comparison, 'criteria', []) as $item)
                <tr>
                    <td>{{ data_get($item, 'dimension', '—') }}</td>
                    <td>{{ data_get($item, 'criterion', '—') }}</td>
                    <td>{{ data_get($item, 'period_a_score') !== null ? number_format((float) data_get($item, 'period_a_score'), 2) : '—' }}</td>
                    <td>{{ data_get($item, 'period_b_score') !== null ? number_format((float) data_get($item, 'period_b_score'), 2) : '—' }}</td>
                    <td>{{ data_get($item, 'delta') !== null ? number_format((float) data_get($item, 'delta'), 2) : '—' }}</td>
                    <td>{{ data_get($item, 'period_a_comment') ?: '—' }}</td>
                    <td>{{ data_get($item, 'period_b_comment') ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay información por criterio.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h4 class="section-title">Comentarios comparados</h4>
    <table>
        <thead>
            <tr>
                <th width="50%">Período A</th>
                <th width="50%">Período B</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Comentario general:</strong><br>
                    {{ data_get($comparison, 'comments.period_a.general_comment') ?: '—' }}
                    <br><br>

                    <strong>Fortalezas:</strong><br>
                    {{ data_get($comparison, 'comments.period_a.strengths') ?: '—' }}
                    <br><br>

                    <strong>Oportunidades de mejora:</strong><br>
                    {{ data_get($comparison, 'comments.period_a.improvement_opportunities') ?: '—' }}
                    <br><br>

                    <strong>Recomendaciones:</strong><br>
                    {{ data_get($comparison, 'comments.period_a.recommendations') ?: '—' }}
                </td>
                <td>
                    <strong>Comentario general:</strong><br>
                    {{ data_get($comparison, 'comments.period_b.general_comment') ?: '—' }}
                    <br><br>

                    <strong>Fortalezas:</strong><br>
                    {{ data_get($comparison, 'comments.period_b.strengths') ?: '—' }}
                    <br><br>

                    <strong>Oportunidades de mejora:</strong><br>
                    {{ data_get($comparison, 'comments.period_b.improvement_opportunities') ?: '—' }}
                    <br><br>

                    <strong>Recomendaciones:</strong><br>
                    {{ data_get($comparison, 'comments.period_b.recommendations') ?: '—' }}
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
