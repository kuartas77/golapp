<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Deportista {{$player->unique_code}}</title>
    <link rel="stylesheet" href="{{ public_path('css/dompdf.css') }}" media="all">
    <link rel="stylesheet" href="{{ public_path('css/dompdf-overrides.css') }}" media="all">
    <link rel="stylesheet" href="{{ public_path('css/pdf-inscription-detail.css') }}" media="all">
</head>
<body class="pdf-inscription-detail">
    @php
        $playerPhoto = $player->photo_pdf_local;
        $genderLabel = $player->gender == 'M' ? 'Masculino' : 'Femenino';
        $paymentColspan = $quarter !== '' ? 1 : 4;
        $playerFullName = trim(($player->names ?? '').' '.($player->last_names ?? ''));
    @endphp

    <table class="table-full pdf-hero no-break">
        <tr>
            <td class="text-left" width="15%">
                <img class="brand-logo" src="{{ $school->logo_local }}" alt="Logo {{ $school->name }}" width="70" height="70">
            </td>
            <td class="text-left" width="60%">
                <span class="eyebrow">Ficha del deportista</span>
                <h1 class="hero-title">{{ $school->name }}</h1>
                <div class="hero-subtitle">Resumen de inscripción, seguimiento deportivo y estado administrativo.</div>

                <table class="table-full hero-meta">
                    <tr>
                        <td width="33.33%">
                            <div class="meta-card">
                                <span class="meta-label">Código:</span>
                                <span class="meta-value">{{ $player->unique_code }}</span>
                            </div>
                        </td>
                        <td width="33.33%">
                            <div class="meta-card">
                                <span class="meta-label">Fecha de registro:</span>
                                <span class="meta-value">{{ optional($player->created_at)->format('d/m/Y') ?: 'Sin registro' }}</span>
                            </div>
                        </td>
                        <td width="33.33%">
                            <div class="meta-card">
                                <span class="meta-label">Periodo:</span>
                                <span class="meta-value">{{ $quarter_text }}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="text-center" width="25%">
                <img class="" src="{{ $playerPhoto }}" alt="Foto {{ $playerFullName ?: $player->unique_code }}" width="70" height="70">
            </td>
        </tr>
    </table>

    <table class="table-full detail detail-lines pdf-section no-break">
        <thead>
            <tr class="section-heading">
                <th colspan="3" class="text-center">Información del deportista</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="33.33%">
                    <span class="info-label">Nombres:</span>
                    <span class="info-value">{{ $player->names ?: 'Sin registro' }}</span>
                </td>
                <td width="33.33%">
                    <span class="info-label">Apellidos:</span>
                    <span class="info-value">{{ $player->last_names ?: 'Sin registro' }}</span>
                </td>
                <td width="33.33%">
                    <span class="info-label">Documento de identidad:</span>
                    <span class="info-value">{{ $player->identification_document ?: 'Sin registro' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">Género:</span>
                    <span class="info-value">{{ $genderLabel }}</span>
                </td>
                <td>
                    <span class="info-label">Fecha de nacimiento:</span>
                    <span class="info-value">{{ $player->date_birth ?: 'Sin registro' }}</span>
                </td>
                <td>
                    <span class="info-label">Lugar de nacimiento:</span>
                    <span class="info-value">{{ $player->place_birth ?: 'Sin registro' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">{{ $player->address ?: 'Sin registro' }}</span>
                </td>
                <td>
                    <span class="info-label">Municipio:</span>
                    <span class="info-value">{{ $player->municipality ?: 'Sin registro' }}</span>
                </td>
                <td>
                    <span class="info-label">Barrio:</span>
                    <span class="info-value">{{ $player->neighborhood ?: 'Sin registro' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">Teléfonos:</span>
                    <span class="info-value">{{ trim(($player->phones ?? '').' '.($player->mobile ?? '')) ?: 'Sin registro' }}</span>
                </td>
                <td>
                    <span class="info-label">Correo electrónico:</span>
                    <span class="info-value">{{ $player->email ?: 'Sin registro' }}</span>
                </td>
                <td>
                    <span class="info-label">EPS:</span>
                    <span class="info-value">{{ $player->eps ?: 'Sin registro' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="info-label">Instituto, colegio o escuela:</span>
                    <span class="info-value">{{ $player->school ?: 'Sin registro' }}</span>
                </td>
                <td>
                    <span class="info-label">Grado</span>
                    <span class="info-value">{{ $player->degree ?: 'Sin registro' }}</span>
                </td>
            </tr>
        </tbody>
    </table>

    @foreach($player->inscriptions as $inscription)
        @php
            $trainingGroupName = optional($inscription->trainingGroup)->name ?: 'Sin grupo asignado';
        @endphp

            <table class="table-full detail detail-lines pdf-section no-break">
                <thead>
                    <tr class="section-heading">
                        <th colspan="4" class="text-center">Resumen deportivo {{ $quarter_text }}</th>
                    </tr>
                    <tr class="subsection-heading">
                        <th colspan="2" class="text-left">Inscripción: {{ $inscription->year }}</th>
                        <th class="text-left">Grupo de entrenamiento: {{ $trainingGroupName }}</th>
                        <th class="text-left">Periodo: {{ $quarter_text }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="25%" class="metric-cell">
                            <span class="metric-label">Total partidos:</span>
                            <span class="metric-value">{{ $inscription->format_average['total_matches'] }}</span>
                        </td>
                        <td width="25%" class="metric-cell">
                            <span class="metric-label">Asistencias a partidos:</span>
                            <span class="metric-value">{{ $inscription->format_average['assistance'] }}</span>
                        </td>
                        <td width="25%" class="metric-cell">
                            <span class="metric-label">Veces titular:</span>
                            <span class="metric-value">{{ $inscription->format_average['titular'] }}</span>
                        </td>
                        <td width="25%" class="metric-cell">
                            <span class="metric-label">Promedio de calificación:</span>
                            <span class="metric-value">{{ $inscription->format_average['qualification'] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="metric-cell">
                            <span class="metric-label">Total de goles:</span>
                            <span class="metric-value">{{ $inscription->format_average['goals'] }}</span>
                        </td>
                        <td class="metric-cell">
                            <span class="metric-label">Promedio de goles por partido:</span>
                            <span class="metric-value">{{ $inscription->format_average['goals_avg'] }}</span>
                        </td>
                        <td class="metric-cell">
                            <span class="metric-label">Total amarillas:</span>
                            <span class="metric-value">{{ $inscription->format_average['yellow_cards'] }}</span>
                        </td>
                        <td class="metric-cell">
                            <span class="metric-label">Promedio amarillas por partido:</span>
                            <span class="metric-value">{{ $inscription->format_average['yellow_cards_avg'] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="metric-cell">
                            <span class="metric-label">Total rojas:</span>
                            <span class="metric-value">{{ $inscription->format_average['red_cards'] }}</span>
                        </td>
                        <td class="metric-cell">
                            <span class="metric-label">Promedio rojas por partido:</span>
                            <span class="metric-value">{{ $inscription->format_average['red_cards_avg'] }}</span>
                        </td>
                        <td class="metric-cell">
                            <span class="metric-label">Minutos jugados:</span>
                            <span class="metric-value">{{ $inscription->format_average['played_approx'] }}</span>
                        </td>
                        <td class="metric-cell">
                            <span class="metric-label">Promedio de minutos por partido</span>
                            <span class="metric-value">{{ $inscription->format_average['played_approx_avg'] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="metric-cell">
                            <span class="metric-label">Posiciones en el campo:</span>
                            <span class="wide-note">{{ $inscription->format_average['positions'] ?: 'Sin registro' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="table-full detail detail-lines pdf-section no-break">
                <thead>
                    <tr class="section-heading">
                        <th colspan="{{ $paymentColspan }}" class="text-center">Estado de mensualidades</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($inscription->payments as $pay)
                    <tr class="subsection-heading">
                        @if($quarter != '')
                        <th class="text-center">Mensualidades {{ $quarter_text }}</th>
                        @else
                        <th colspan="4" class="text-center">Mensualidades año: {{ $pay->year }}</th>
                        @endif
                    </tr>
                    <tr>
                        @if($quarter == 'quarter_one' || $quarter == '')
                        <td class="bold payment-cell {{$pay->january == '2' ? 'error': ''}}">Enero: {{ getPay($pay->january) }}</td>
                        @endif
                        @if($quarter == 'quarter_two' || $quarter == '')
                        <td class="bold payment-cell {{$pay->april == '2' ? 'error': ''}}">Abril: {{ getPay($pay->april) }}</td>
                        @endif
                        @if($quarter == 'quarter_three' || $quarter == '')
                        <td class="bold payment-cell {{$pay->july == '2' ? 'error': ''}}">Julio: {{ getPay($pay->july) }}</td>
                        @endif
                        @if($quarter == 'quarter_four' || $quarter == '')
                        <td class="bold payment-cell {{$pay->october == '2' ? 'error': ''}}">Octubre: {{ getPay($pay->october) }}</td>
                        @endif
                    </tr>
                    <tr>
                        @if($quarter == 'quarter_one' || $quarter == '')
                        <td class="bold payment-cell {{$pay->february == '2' ? 'error': ''}}">Febrero: {{ getPay($pay->february) }}</td>
                        @endif
                        @if($quarter == 'quarter_two' || $quarter == '')
                        <td class="bold payment-cell {{$pay->may == '2' ? 'error': ''}}">Mayo: {{ getPay($pay->may) }}</td>
                        @endif
                        @if($quarter == 'quarter_three' || $quarter == '')
                        <td class="bold payment-cell {{$pay->august == '2' ? 'error': ''}}">Agosto: {{ getPay($pay->august) }}</td>
                        @endif
                        @if($quarter == 'quarter_four' || $quarter == '')
                        <td class="bold payment-cell {{$pay->november == '2' ? 'error': ''}}">Noviembre: {{ getPay($pay->november) }}</td>
                        @endif
                    </tr>
                    <tr>
                        @if($quarter == 'quarter_one' || $quarter == '')
                        <td class="bold payment-cell {{$pay->march == '2' ? 'error': ''}}">Marzo: {{ getPay($pay->march) }}</td>
                        @endif
                        @if($quarter == 'quarter_two' || $quarter == '')
                        <td class="bold payment-cell {{$pay->june == '2' ? 'error': ''}}">Junio: {{ getPay($pay->june) }}</td>
                        @endif
                        @if($quarter == 'quarter_three' || $quarter == '')
                        <td class="bold payment-cell {{$pay->september == '2' ? 'error': ''}}">Septiembre: {{ getPay($pay->september) }}</td>
                        @endif
                        @if($quarter == 'quarter_four' || $quarter == '')
                        <td class="bold payment-cell {{$pay->december == '2' ? 'error': ''}}">Diciembre: {{ getPay($pay->december) }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        @if($quarter != '')
                        <th class="text-center muted-note">Sin registro de pagos.</th>
                        @else
                        <th colspan="4" class="text-center muted-note">Sin registro de pagos.</th>
                        @endif
                    </tr>
                @endforelse
                </tbody>
            </table>

            <table class="table-full detail detail-lines pdf-section no-break">
                @php
                    $totalSpan = 25;
                @endphp
                <thead>
                    <tr class="section-heading">
                        <th colspan="26" class="text-center">Asistencias de entrenamiento</th>
                        <th class="text-center bold">%</th>
                    </tr>
                    <tr class="subsection-heading">
                        <th colspan="27" class="text-left">Grupo: <span class="text-uppercase">{{ $trainingGroupName }}</span></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($inscription->assistance as $assistance)
                    @php
                        $countAS = 0;
                        $classCount = $assistance->classDays->count();
                        $colspan = ($totalSpan - $classCount);
                    @endphp
                    <tr class="subsection-heading">
                        <td class="bold">Clase #</td>
                        @for ($index = 1; $index <= $classCount; $index++)
                            <td class="text-center bold">{{$index}}</td>
                        @endfor
                        <td colspan="{{ $colspan }}">&nbsp;</td>
                        <td class="text-center bold">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="bold">Mes: {{ $assistance->month }}</td>
                        @for ($index = 1; $index <= $classCount; $index++)
                            @php
                                $column = numbersToLetters($index);
                                $countAS += $assistance->$column == 1 ? 1 : 0;
                            @endphp
                            <td class="text-center bold {{$assistance->$column == 2 ? 'error':''}} {{$assistance->$column == 'as' ? 'success':''}}" >
                            {!! $assistance->$column == null ? '': $optionAssist[$assistance->$column] !!}
                            </td>
                        @endfor
                        <td colspan="{{ $colspan }}">&nbsp;</td>
                        <td class="text-center bold {{ $countAS == 0 ? 'error' : '' }}">{{ percent($countAS, $classCount) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="27" class="text-center muted-note">Sin registros de asistencia.</th>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <table class="table-full detail detail-lines pdf-section legend-table no-break">
                <tr class="subsection-heading">
                    <td class="text-center success"><strong>ASISTENCIA:&#10003;</strong></td>
                    <td class="text-center error"><strong>FALTA:&#10008;</strong></td>
                    <td class="text-center"><strong>EXCUSA:E</strong></td>
                    <td class="text-center"><strong>RETIRO:R</strong></td>
                    <td class="text-center"><strong>INCAPACIDAD:I</strong></td>
                </tr>
            </table>

            @if($observations_assists->isNotEmpty())
            <table class="table-full detail detail-lines pdf-section no-break">
                <thead>
                    <tr class="section-heading">
                        <th class="text-center">Observaciones de entrenamientos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($observations_assists as $assist)
                        @foreach($assist->observations as $date => $observation)
                        <tr>
                            <td class="observation-cell">
                                <span class="observation-date">{{ $date }}</span>
                                <span> {{ $observation }}</span>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($observations_skills->isNotEmpty())
            <table class="table-full detail detail-lines pdf-section no-break">
                <thead>
                    <tr class="section-heading">
                        <th class="text-center">Observaciones de competencias</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($observations_skills as $skill)
                    <tr>
                        <td class="observation-cell">
                            <span class="observation-date">Fecha: {{ $skill->created_at->format('Y-m-d') }}</span>
                            <span> {{ $skill->observation }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
    @endforeach
</body>
</html>
