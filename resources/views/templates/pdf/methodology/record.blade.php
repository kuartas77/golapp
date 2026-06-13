<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }} - {{ $record->title }}</title>
    <link rel="stylesheet" href="{{ public_path('css/dompdf.css') }}">
    <link rel="stylesheet" href="{{ public_path('css/dompdf-overrides.css') }}">
    <style>
        body {
            color: #111;
            font-size: 9px;
        }

        .methodology-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .methodology-subtitle {
            font-size: 10px;
            font-weight: bold;
        }

        .methodology-table {
            border-collapse: collapse;
            margin: 4px 0;
            width: 100%;
        }

        .methodology-table td,
        .methodology-table th {
            border: 1px solid #343434;
            padding: 4px;
            vertical-align: top;
        }

        .methodology-heading {
            background: #b7b7b7;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .field-label {
            background: #eeeeee;
            font-weight: bold;
            width: 18%;
        }

        .field-value {
            min-height: 18px;
            white-space: pre-line;
        }

        .phase-field {
            width: 40%;
        }

        .phase-dosage {
            width: 18%;
        }

        .phase-description {
            width: 32%;
        }

        .phase-time {
            width: 10%;
        }

        .small-muted {
            color: #555;
            font-size: 8px;
        }
    </style>
</head>

<body>
    @php
        $fields = $record->fields ?? [];
        $diagrams = $record->diagrams ?? [];
        $fieldValue = fn (string $key) => data_get($fields, $key, '');
        $diagramItems = fn (string $key) => data_get($diagrams, $key, []);
    @endphp

    <table class="table-full title">
        <tr>
            <td class="text-left" width="18%">
                <img src="{{ $school->logo_local }}" width="58" height="58">
            </td>
            <td class="text-center" width="64%">
                <div class="methodology-title">{{ $school->name }}</div>
                <div class="methodology-subtitle">{{ $title }}</div>
                <div>{{ $record->title }}</div>
            </td>
            <td class="text-right" width="18%">
                <img src="{{ $school->logo_local }}" width="58" height="58">
            </td>
        </tr>
    </table>

    <table class="methodology-table">
        <tr>
            <td class="field-label">Creado por</td>
            <td>{{ $record->user?->name }}</td>
            <td class="field-label">Grupo</td>
            <td>{{ $record->trainingGroup?->name ?? 'Sin grupo' }}</td>
            <td class="field-label">Fecha</td>
            <td>{{ $record->created_at?->format('Y-m-d') }}</td>
        </tr>
    </table>

    @if($record->type === \App\Models\MethodologyRecord::TYPE_PLANNING)
        @foreach($fieldGroups as $group)
            @continue($group['title'] === 'Cierre')
            <table class="methodology-table">
                <tr>
                    <td class="methodology-heading" colspan="4">{{ $group['title'] }}</td>
                </tr>
                @foreach(array_chunk($group['fields'], 2) as $row)
                    <tr>
                        @foreach($row as $field)
                            <td class="field-label">{{ $field['label'] }}</td>
                            <td class="field-value">{{ $fieldValue($field['key']) }}</td>
                        @endforeach
                        @if(count($row) === 1)
                            <td class="field-label">&nbsp;</td>
                            <td>&nbsp;</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endforeach

        <table class="methodology-table">
            <tr>
                <td class="methodology-heading" colspan="4">Fases de planificación</td>
            </tr>
            <tr>
                <td class="methodology-heading phase-field">Cancha</td>
                <td class="methodology-heading phase-time">Tiempo</td>
                <td class="methodology-heading phase-dosage">Dosificación</td>
                <td class="methodology-heading phase-description">Descripción</td>
            </tr>
            @foreach($planningPhases as $phase)
                <tr>
                    <td class="phase-field">
                        {{--<div class="text-center bold">{{ $phase['label'] }}</div>--}}
                        @include('templates.pdf.methodology.partials.field-diagram', [
                            'items' => $diagramItems($phase['key']),
                        ])
                    </td>
                    <td class="phase-time field-value">{{ $fieldValue($phase['time']) }}</td>
                    <td class="phase-dosage field-value">{{ $fieldValue($phase['dosage']) }}</td>
                    <td class="phase-description field-value">{{ $fieldValue($phase['description']) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="methodology-heading" colspan="4">Fase final</td>
            </tr>
            <tr>
                <td class="field-label">Tiempo</td>
                <td class="field-value" colspan="3">{{ $fieldValue('final_phase_time') }}</td>
            </tr>
            <tr>
                <td class="field-label">Dosificación</td>
                <td class="field-value" colspan="3">{{ $fieldValue('final_phase_dosage') }}</td>
            </tr>
            <tr>
                <td class="field-label">Descripción</td>
                <td class="field-value" colspan="3">{{ $fieldValue('final_phase_description') }}</td>
            </tr>
        </table>

        @foreach($fieldGroups as $group)
            @if($group['title'] === 'Cierre')
                <table class="methodology-table">
                    <tr>
                        <td class="methodology-heading" colspan="4">{{ $group['title'] }}</td>
                    </tr>
                    @foreach(array_chunk($group['fields'], 2) as $row)
                        <tr>
                            @foreach($row as $field)
                                <td class="field-label">{{ $field['label'] }}</td>
                                <td class="field-value">{{ $fieldValue($field['key']) }}</td>
                            @endforeach
                            @if(count($row) === 1)
                                <td class="field-label">&nbsp;</td>
                                <td>&nbsp;</td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            @endif
        @endforeach
    @elseif($record->type === \App\Models\MethodologyRecord::TYPE_CHARACTERIZATION_SHEET)
        <table class="methodology-table">
            <tr>
                <td class="field-label">Categoría</td>
                <td class="field-value">{{ $fieldValue('category') }}</td>
                <td class="field-label">Año-semestre</td>
                <td class="field-value">{{ $fieldValue('year_semester') }}</td>
            </tr>
            <tr>
                <td class="field-label">Grupo etario</td>
                <td class="field-value">{{ $fieldValue('age_group') }}</td>
                <td class="field-label">Competencias 2026</td>
                <td class="field-value">{{ $fieldValue('competitions') }}</td>
            </tr>
            <tr>
                <td class="field-label">Objetivos deportivos 2026 (entrenador)</td>
                <td class="field-value" colspan="3">{{ $fieldValue('sport_objectives') }}</td>
            </tr>
            <tr>
                <td class="field-label">Objetivos formativos de la categoría año 2026</td>
                <td class="field-value" colspan="3">{{ $fieldValue('formative_objectives') }}</td>
            </tr>
            <tr>
                <td class="methodology-heading" colspan="4">Valores constitutivos de la categoría</td>
            </tr>
            <tr>
                <td class="field-value" colspan="4">{{ $fieldValue('constitutive_values') }}</td>
            </tr>
            <tr>
                <td class="methodology-heading" colspan="4">Idiosincracia de la categoría</td>
            </tr>
            <tr>
                <td class="methodology-heading">Esquemas tácticos habituales</td>
                <td class="methodology-heading">Modelo de juego</td>
                <td class="methodology-heading">Principios ofensivos y defensivos trabajados</td>
                <td class="methodology-heading">Elementos técnicos prioritarios</td>
            </tr>
            <tr>
                <td class="field-value">{{ $fieldValue('tactical_schemes') }}</td>
                <td class="field-value">{{ $fieldValue('game_model') }}</td>
                <td class="field-value">{{ $fieldValue('offensive_defensive_principles') }}</td>
                <td class="field-value">{{ $fieldValue('priority_technical_elements') }}</td>
            </tr>
            <tr>
                <td class="methodology-heading" colspan="4">Reglamento interno de la categoría</td>
            </tr>
            <tr>
                <td class="field-value" colspan="4">{{ $fieldValue('internal_rules') }}</td>
            </tr>
        </table>

        <table class="methodology-table">
            <tr>
                <td class="methodology-heading" colspan="3">Prescripción médica de jugadores</td>
            </tr>
            <tr>
                <td class="methodology-heading" width="10%">N°</td>
                <td class="methodology-heading" width="45%">Nombre del jugador</td>
                <td class="methodology-heading" width="45%">Condición</td>
            </tr>
            @for($row = 1; $row <= 3; $row++)
                <tr>
                    <td class="text-center">{{ $row }}</td>
                    <td class="field-value">{{ $fieldValue("medical_prescription_player_{$row}_name") }}</td>
                    <td class="field-value">{{ $fieldValue("medical_prescription_player_{$row}_condition") }}</td>
                </tr>
            @endfor
        </table>

        <table class="methodology-table">
            <tr>
                <td class="methodology-heading" colspan="3">Jugadores con proyección</td>
            </tr>
            <tr>
                <td class="methodology-heading" width="10%">N°</td>
                <td class="methodology-heading" width="45%">Nombre del jugador</td>
                <td class="methodology-heading" width="45%">Cualidades</td>
            </tr>
            @for($row = 1; $row <= 3; $row++)
                <tr>
                    <td class="text-center">{{ $row }}</td>
                    <td class="field-value">{{ $fieldValue("projection_player_{$row}_name") }}</td>
                    <td class="field-value">{{ $fieldValue("projection_player_{$row}_qualities") }}</td>
                </tr>
            @endfor
        </table>
    @elseif($record->type === \App\Models\MethodologyRecord::TYPE_MONTHLY_REPORT)
        <table class="methodology-table">
            <tr>
                <td class="field-label">Entrenador</td>
                <td class="field-value">{{ $fieldValue('coach') }}</td>
            </tr>
            <tr>
                <td class="field-label">Categoría</td>
                <td class="field-value">{{ $fieldValue('category') }}</td>
            </tr>
            <tr>
                <td class="field-label">Mes correspondiente al informe</td>
                <td class="field-value">{{ $fieldValue('report_month') }}</td>
            </tr>
        </table>

        <table class="methodology-table">
            <tr>
                <td class="methodology-heading" width="7%">N°</td>
                <td class="methodology-heading" width="38%">Obligaciones del entrenador</td>
                <td class="methodology-heading" width="33%">Actividad realizada</td>
                <td class="methodology-heading" width="22%">Soporte</td>
            </tr>
            @foreach([
                ['number' => 1, 'obligation' => 'Planear las sesiones de entrenamiento del mes en curso (plazo específico).'],
                ['number' => 2, 'obligation' => 'Registrar diariamente la asistencia de la categoría a cargo.'],
                ['number' => 3, 'obligation' => 'Realizar el debido seguimiento a los jugadores que no asistieron en el mes.'],
                ['number' => 4, 'obligation' => 'Realizar seguimiento al jugador que ingresa a la categoría desde la clase de cortesía hasta llevar a cabo el proceso de inscripción.'],
                ['number' => 5, 'obligation' => 'Actualización diaria de la base de datos de los jugadores.'],
                ['number' => 6, 'obligation' => 'Actualización constante de los grupos de whatsapp, jugador que ingrese y jugador que se retire.'],
                ['number' => 7, 'obligation' => 'Asistencia a las reuniones y capacitaciones programadas.'],
            ] as $row)
                <tr>
                    <td class="text-center">{{ $row['number'] }}</td>
                    <td class="field-value">{{ $row['obligation'] }}</td>
                    <td class="field-value">{{ $fieldValue("coach_obligation_{$row['number']}_activity") }}</td>
                    <td class="field-value">{{ $fieldValue("coach_obligation_{$row['number']}_support") }}</td>
                </tr>
            @endforeach
        </table>

        <table class="methodology-table">
            <tr>
                <td class="field-label">Responsable</td>
                <td class="field-value">{{ $record->user?->name }}</td>
            </tr>
            @if($record->user?->profile?->identification_document)
                <tr>
                    <td class="field-label">Documento</td>
                    <td class="field-value">{{ $record->user->profile->identification_document }}</td>
                </tr>
            @endif
        </table>
    @elseif($record->type === \App\Models\MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT)
        <table class="methodology-table">
            <tr>
                <td class="field-label">Entrenador</td>
                <td class="field-value">{{ $fieldValue('coach') }}</td>
            </tr>
            <tr>
                <td class="field-label">Categoría</td>
                <td class="field-value">{{ $fieldValue('category') }}</td>
            </tr>
            <tr>
                <td class="field-label">Mes correspondiente al informe</td>
                <td class="field-value">{{ $fieldValue('report_month') }}</td>
            </tr>
        </table>

        <table class="methodology-table">
            <tr>
                <td class="methodology-heading" width="8%">N°</td>
                <td class="methodology-heading" width="45%">Reporte</td>
                <td class="methodology-heading" width="47%">Descripción</td>
            </tr>
            @foreach([
                ['number' => 1, 'report' => 'Objetivos planteados en el mes en curso.', 'key' => 'monthly_objectives_description'],
                ['number' => 2, 'report' => 'Logros obtenidos en el mes en curso.', 'key' => 'monthly_achievements_description'],
                ['number' => 3, 'report' => 'Dificultades presentadas en el mes en curso.', 'key' => 'monthly_difficulties_description'],
                ['number' => 4, 'report' => 'Valores deportivos abordados', 'key' => 'sport_values_description'],
                ['number' => 5, 'report' => 'Situaciones o novedades específicas con jugadores. (enfermedad, incapacidad, lesión, evolución deportiva o entre otras).', 'key' => 'specific_player_news_description'],
                ['number' => 6, 'report' => 'Seguimiento y/o control que se llevó o se está llevando a cabo con el jugador.', 'key' => 'player_follow_up_description'],
            ] as $row)
                <tr>
                    <td class="text-center">{{ $row['number'] }}</td>
                    <td class="field-value">{{ $row['report'] }}</td>
                    <td class="field-value">{{ $fieldValue($row['key']) }}</td>
                </tr>
            @endforeach
        </table>

        <table class="methodology-table">
            <tr>
                <td class="field-label">Responsable</td>
                <td class="field-value">{{ $record->user?->name }}</td>
            </tr>
            @if($record->user?->profile?->identification_document)
                <tr>
                    <td class="field-label">Documento</td>
                    <td class="field-value">{{ $record->user->profile->identification_document }}</td>
                </tr>
            @endif
        </table>
    @else
        @foreach($fieldGroups as $group)
            <table class="methodology-table">
                <tr>
                    <td class="methodology-heading" colspan="2">{{ $group['title'] }}</td>
                </tr>
                @foreach($group['fields'] as $field)
                    <tr>
                        <td class="field-label">{{ $field['label'] }}</td>
                        <td class="field-value">{{ $fieldValue($field['key']) }}</td>
                    </tr>
                @endforeach
            </table>
        @endforeach
    @endif
</body>
</html>
