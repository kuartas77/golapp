<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comparativo de Evaluaciones</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10.5px;
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
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .summary-box td {
            border: 1px solid #dcdcdc;
            padding: 10px;
            vertical-align: top;
        }

        .score-big {
            font-size: 18px;
            font-weight: bold;
        }

        .muted {
            color: #666;
        }

        .up {
            font-weight: bold;
        }

        .down {
            font-weight: bold;
        }

        .equal {
            font-weight: bold;
        }

        .small {
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    @php
        $periodA = $comparison['period_a'];
        $periodB = $comparison['period_b'];
        $overall = $comparison['overall'];
        $dimensions = $comparison['dimensions'];
        $criteria = $comparison['criteria'];
        $comments = $comparison['comments'];

        $trendLabel = function ($trend) {
            return match ($trend) {
                'up' => 'Mejoró',
                'down' => 'Disminuyó',
                default => 'Sin cambio',
            };
        };

        $deltaLabel = function ($delta) {
            if ($delta === null) {
                return 'N/A';
            }

            $prefix = $delta > 0 ? '+' : '';
            return $prefix . number_format((float) $delta, 2);
        };
    @endphp

    <div class="header">
        <div class="title">{{ $clubName }}</div>
        <div class="subtitle">Comparativo de evaluaciones para acudiente</div>
    </div>

    <div class="section">
        <div class="section-title">Datos generales</div>

        <table class="grid">
            <tr>
                <th width="20%">Jugador</th>
                <td width="30%">{{ $comparison['player']['name'] ?? 'N/A' }}</td>
                <th width="20%">Grupo</th>
                <td width="30%">{{ $comparison['inscription']['training_group_name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Temporada</th>
                <td>{{ $comparison['inscription']['year'] ?? 'N/A' }}</td>
                <th>Documento</th>
                <td>Comparativo entre períodos</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Resumen general</div>

        <table class="summary-box">
            <tr>
                <td width="33%">
                    <div class="muted">{{ $periodA['period_name'] ?? 'Período A' }}</div>
                    <div class="score-big">{{ number_format((float) ($overall['period_a_score'] ?? 0), 2) }}</div>
                    <div class="small">
                        Fecha:
                        {{ !empty($periodA['evaluated_at']) ? \Carbon\Carbon::parse($periodA['evaluated_at'])->format('Y-m-d H:i') : 'N/A' }}
                    </div>
                </td>
                <td width="33%">
                    <div class="muted">{{ $periodB['period_name'] ?? 'Período B' }}</div>
                    <div class="score-big">{{ number_format((float) ($overall['period_b_score'] ?? 0), 2) }}</div>
                    <div class="small">
                        Fecha:
                        {{ !empty($periodB['evaluated_at']) ? \Carbon\Carbon::parse($periodB['evaluated_at'])->format('Y-m-d H:i') : 'N/A' }}
                    </div>
                </td>
                <td width="34%">
                    <div class="muted">Variación general</div>
                    <div class="score-big">{{ $deltaLabel($overall['delta'] ?? null) }}</div>
                    <div>
                        Tendencia:
                        <span class="{{ $overall['trend'] ?? 'equal' }}">
                            {{ $trendLabel($overall['trend'] ?? 'equal') }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Comparativo por dimensión</div>

        <table class="grid">
            <thead>
                <tr>
                    <th width="28%">Dimensión</th>
                    <th width="18%">{{ $periodA['period_name'] ?? 'Período A' }}</th>
                    <th width="18%">{{ $periodB['period_name'] ?? 'Período B' }}</th>
                    <th width="18%">Variación</th>
                    <th width="18%">Tendencia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dimensions as $dimension)
                    <tr>
                        <td>{{ $dimension['dimension'] }}</td>
                        <td>{{ $dimension['period_a_score'] !== null ? number_format((float) $dimension['period_a_score'], 2) : 'N/A' }}</td>
                        <td>{{ $dimension['period_b_score'] !== null ? number_format((float) $dimension['period_b_score'], 2) : 'N/A' }}</td>
                        <td>{{ $deltaLabel($dimension['delta']) }}</td>
                        <td>{{ $trendLabel($dimension['trend']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No hay información para comparar por dimensión.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Comentarios del entrenador</div>

        <table class="grid">
            <thead>
                <tr>
                    <th width="24%">Campo</th>
                    <th width="38%">{{ $periodA['period_name'] ?? 'Período A' }}</th>
                    <th width="38%">{{ $periodB['period_name'] ?? 'Período B' }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Comentario general</td>
                    <td>{{ $comments['period_a']['general_comment'] ?? 'Sin comentario.' }}</td>
                    <td>{{ $comments['period_b']['general_comment'] ?? 'Sin comentario.' }}</td>
                </tr>
                <tr>
                    <td>Fortalezas</td>
                    <td>{{ $comments['period_a']['strengths'] ?? 'No registradas.' }}</td>
                    <td>{{ $comments['period_b']['strengths'] ?? 'No registradas.' }}</td>
                </tr>
                <tr>
                    <td>Oportunidades de mejora</td>
                    <td>{{ $comments['period_a']['improvement_opportunities'] ?? 'No registradas.' }}</td>
                    <td>{{ $comments['period_b']['improvement_opportunities'] ?? 'No registradas.' }}</td>
                </tr>
                <tr>
                    <td>Recomendaciones</td>
                    <td>{{ $comments['period_a']['recommendations'] ?? 'No registradas.' }}</td>
                    <td>{{ $comments['period_b']['recommendations'] ?? 'No registradas.' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">Comparativo detallado por criterio</div>

        <table class="grid">
            <thead>
                <tr>
                    <th width="16%">Dimensión</th>
                    <th width="22%">Criterio</th>
                    <th width="10%">{{ $periodA['period_name'] ?? 'A' }}</th>
                    <th width="10%">{{ $periodB['period_name'] ?? 'B' }}</th>
                    <th width="10%">Variación</th>
                    <th width="12%">Tendencia</th>
                    <th width="20%">Observación relevante</th>
                </tr>
            </thead>
            <tbody>
                @forelse($criteria as $item)
                    <tr>
                        <td>{{ $item['dimension'] ?? 'N/A' }}</td>
                        <td>{{ $item['criterion'] ?? 'N/A' }}</td>
                        <td>{{ $item['period_a_score'] !== null ? number_format((float) $item['period_a_score'], 2) : 'N/A' }}</td>
                        <td>{{ $item['period_b_score'] !== null ? number_format((float) $item['period_b_score'], 2) : 'N/A' }}</td>
                        <td>{{ $deltaLabel($item['delta']) }}</td>
                        <td>{{ $trendLabel($item['trend']) }}</td>
                        <td>
                            @if(!empty($item['period_b_comment']))
                                {{ $item['period_b_comment'] }}
                            @elseif(!empty($item['period_a_comment']))
                                {{ $item['period_a_comment'] }}
                            @else
                                Sin observación.
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay información de criterios para comparar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section small muted">
        Documento generado automáticamente por {{ $clubName }} el {{ now()->format('Y-m-d H:i') }}.
    </div>

</body>
</html>