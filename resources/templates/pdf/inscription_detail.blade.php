<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Deportista {{$player->unique_code}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}" media="all">
</head>
<body>
    <table class="table-full title">
        <tr>
            <td class="text-left" width="20%">
                <img src="{{ $school->logo_local }}" width="70" height="70">
            </td>
            <td class="text-center school-title" width="60%">{{ $school->name }}<br>FICHA DEL DEPORTISTA
            </td>
            <td class="text-right" width="20%">
                <img src="{{ $player->photo ? storage_path('app/public/'.$player->photo) : public_path('img/user.png') }}" width="70" height="70">
            </td>
        </tr>
        <tr class="tr-tit">
            <td class="text-center bold" width="45%">
                <h3 class="school-title">Fecha De Registro: {{ $player->created_at->format('Y-m-d') }}</h3>
            </td>

            <td class="text-center" width="10%"></td>

            <td class="text-center bold" width="45%">
                <h3 class="school-title">Código: {{ $player->unique_code }}</h3>
            </td>
        </tr>
    </table>

    <table class="table-full detail detail-lines">
        <thead>
            <tr class="tr-tit">
                <th colspan="3" class="text-center"><strong class="bold">Deportista</strong></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class=""><strong class="bold">&nbsp;Nombres:</strong> {{ $player->names }}</td>
                <td class=""><strong class="bold">&nbsp;Apellidos:</strong> {{ $player->last_names }}</td>
                <td class=""><strong class="bold">&nbsp;Doc. de Identidad:</strong> {{ $player->identification_document }}</td>
            </tr>
            <tr>
                @if($player->gender == 'M')
                    <td class=""><strong class="bold">&nbsp;Genero:</strong> Masculino</td>
                @else
                    <td class=""><strong class="bold">&nbsp;Genero:</strong> Femenino</td>
                @endif
                <td class=""><strong class="bold">&nbsp;Fecha de Nacimiento:</strong> {{ $player->date_birth }}</td>
                <td class=""><strong class="bold">&nbsp;Lugar de Nacimiento:</strong> {{ $player->place_birth }}</td>
            </tr>
            <tr>
                <td class=""><strong class="bold">&nbsp;Dirección:</strong> {{ $player->address }}</td>
                <td class=""><strong class="bold">&nbsp;Municipio:</strong> {{ $player->municipality }}</td>
                <td class=""><strong class="bold">&nbsp;Barrio:</strong> {{ $player->neighborhood }}</td>
            </tr>
            <tr>
                <td class=""><strong class="bold">&nbsp;Teléfonos:</strong> {{ $player->phones }} {{ $player->mobile }}</td>
                <td class=""><strong class="bold">&nbsp;Correo Electrónico:</strong> {{ $player->email }}</td>
                <td class=""><strong class="bold">&nbsp;EPS:</strong> {{ $player->eps }}</td>
            </tr>
            <tr>
                <td class="" colspan="2"><strong class="bold">&nbsp;Instituto/Colegio/Escuela:</strong> {{ $player->school }}</td>
                <td class=""><strong class="bold">&nbsp;Grado:</strong> {{ $player->degree }}</td>
            </tr>
        </tbody>
    </table>

        @foreach($player->inscriptions as $inscription)
            <!-- End Statistics -->
            <table class="table-full detail detail-lines">
                <thead>
                    <tr class="tr-tit">
                        <th colspan="2" class="text-center">Estadisticas {{$quarter_text}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>&nbsp;<strong class="bold">Total Partidos:</strong> {{ $inscription->format_average['total_matches'] }}</td>
                        <td>&nbsp;<strong class="bold"># Asistencias A Partidos:</strong> {{ $inscription->format_average['assistance'] }}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;<strong class="bold">Total Titular:</strong> {{ $inscription->format_average['titular'] }}</td>
                        <td>&nbsp;<strong class="bold">Promedio De Calificación:</strong> {{ $inscription->format_average['qualification'] }}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;<strong class="bold">Total De Goles:</strong> {{ $inscription->format_average['goals'] }}</td>
                        <td>&nbsp;<strong class="bold">Promedio De Goles:</strong> {{ $inscription->format_average['goals_avg'] }}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;<strong class="bold">Total Amarillas:</strong> {{ $inscription->format_average['yellow_cards'] }}</td>
                        <td>&nbsp;<strong class="bold">Promedio Amarillas:</strong> {{ $inscription->format_average['yellow_cards_avg'] }}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;<strong class="bold">Total Rojas:</strong> {{ $inscription->format_average['red_cards'] }}</td>
                        <td>&nbsp;<strong class="bold">Promedio Rojas:</strong> {{ $inscription->format_average['red_cards_avg'] }}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;<strong class="bold">Minutos Jugados:</strong> {{ $inscription->format_average['played_approx'] }}</td>
                        <td>&nbsp;<strong class="bold">Promedio de Minutos Jugados:</strong> {{ $inscription->format_average['played_approx_avg'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong class="bold">&nbsp;Posiciones En el Campo:</strong> {{ $inscription->format_average['positions'] }}</td>
                    </tr>
                </tbody>
            </table>
            <!-- End Statistics -->
            <!-- Payments -->
            <table class="table-full detail detail-lines">
                <tbody>
                @forelse ($inscription->payments as $pay)
                    <tr class="tr-tit">
                        @if($quarter != '')
                        <th class="text-center">Mensualidades {{$quarter_text}}</th>
                        @else
                        <th colspan="4" class="text-center">Mensualidades Año: {{$pay->year}}</th>
                        @endif
                    </tr>
                    <tr>
                        @if($quarter == 'quarter_one' || $quarter == '')
                        <td class="bold {{$pay->january == '2' ? 'error': ''}}">&nbsp;Enero: {{getPay($pay->january)}}</td>
                        @endif
                        @if($quarter == 'quarter_two' || $quarter == '')
                        <td class="bold {{$pay->april == '2' ? 'error': ''}}">&nbsp;Abril: {{getPay($pay->april)}}</td>
                        @endif
                        @if($quarter == 'quarter_three' || $quarter == '')
                        <td class="bold {{$pay->july == '2' ? 'error': ''}}">&nbsp;Julio: {{getPay($pay->july)}}</td>
                        @endif
                        @if($quarter == 'quarter_four' || $quarter == '')
                        <td class="bold {{$pay->october == '2' ? 'error': ''}}">&nbsp;Octubre: {{getPay($pay->october)}}</td>
                        @endif
                    </tr>
                    <tr>
                        @if($quarter == 'quarter_one' || $quarter == '')
                        <td class="bold {{$pay->february == '2' ? 'error': ''}}">&nbsp;Febrero: {{getPay($pay->february)}}</td>
                        @endif
                        @if($quarter == 'quarter_two' || $quarter == '')
                        <td class="bold {{$pay->may == '2' ? 'error': ''}}">&nbsp;Mayo: {{getPay($pay->may)}}</td>
                        @endif
                        @if($quarter == 'quarter_three' || $quarter == '')
                        <td class="bold {{$pay->august == '2' ? 'error': ''}}">&nbsp;Agosto: {{getPay($pay->august)}}</td>
                        @endif
                        @if($quarter == 'quarter_four' || $quarter == '')
                        <td class="bold {{$pay->november == '2' ? 'error': ''}}">&nbsp;Noviembre: {{getPay($pay->november)}}</td>
                        @endif
                    </tr>
                    <tr>
                        @if($quarter == 'quarter_one' || $quarter == '')
                        <td class="bold {{$pay->march == '2' ? 'error': ''}}">&nbsp;Marzo: {{getPay($pay->march)}}</td>
                        @endif
                        @if($quarter == 'quarter_two' || $quarter == '')
                        <td class="bold {{$pay->june == '2' ? 'error': ''}}">&nbsp;Junio: {{getPay($pay->june)}}</td>
                        @endif
                        @if($quarter == 'quarter_three' || $quarter == '')
                        <td class="bold {{$pay->september == '2' ? 'error': ''}}">&nbsp;Septiembre: {{getPay($pay->september)}}</td>
                        @endif
                        @if($quarter == 'quarter_four' || $quarter == '')
                        <td class="bold {{$pay->december == '2' ? 'error': ''}}">&nbsp;Diciembre: {{getPay($pay->december)}}</td>
                        @endif
                    </tr>

                @empty
                    <tr>
                        @if($quarter != '')
                        <th class="text-center">Sin Registro de pagos.</th>
                        @else
                        <th colspan="4" class="text-center">Sin Registro de pagos.</th>
                        @endif
                    </tr>
                @endforelse
                </tbody>
            </table>
            <!-- End Payments -->
            <!-- Asists -->
            <table class="table-full detail detail-lines">
                @php
                    $colspan = 20;
                @endphp
                <thead>
                    <tr class="tr-tit">
                        <th colspan="{{$colspan}}" class="text-center bold">Asistencias Entrenamientos: <strong style="text-transform: uppercase;">{{$inscription->trainingGroup->name}}</strong></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($inscription->assistance as $assistance)
                    @php
                        $countAS = 0;
                    @endphp
                    <tr class="tr-tit">
                        <td class="bold" >&nbsp;Días</td>
                        @for ($index = 1; $index <= $assistance->classDays->count(); $index++)
                            <th class="text-center bold">{{$index}}</th>
                        @endfor
                        <td colspan="{{(($colspan - 2) - $assistance->classDays->count())}}"></td>
                        <td class="text-center bold"></td>
                    </tr>
                    <tr>
                        <td class="bold" >&nbsp;Mes: {{ $assistance->month }}</td>
                        @for ($index = 1; $index <= $assistance->classDays->count(); $index++)
                            @php
                                $column = numbersToLetters($index);
                                $countAS += $assistance->$column == 'as' ? 1 : 0;
                            @endphp
                            <td class="text-center bold {{$assistance->$column == 'fa' ? 'error':''}}" >
                            {{ $assistance->$column == null ? '': $optionAssist[$assistance->$column] }}
                            </td>
                        @endfor
                        <td colspan="{{(($colspan - 2) - $assistance->classDays->count())}}"></td>
                        <td class="text-center bold">&nbsp;{{percent($countAS, $assistance->classDays->count())}}%</td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="20" align="center">Sin Registros De Asistencia</th>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <table class="table-full detail detail-lines">
                <tr class="tr-tit">
                    <td class="text-center"><strong>ASISTENCIA:X</strong></td>
                    <td class="text-center error"><strong>FALTA:F</strong></td>
                    <td class="text-center"><strong>EXCUSA:E</strong></td>
                    <td class="text-center"><strong>RETIRO:R</strong></td>
                    <td class="text-center"><strong>INCAPACIDAD:I</strong></td>
                </tr>
            </table>
            <!-- End Asists -->
            @if($observations_assists->isNotEmpty())
            <table class="table-full detail detail-lines">
                <thead>
                    <tr class="tr-tit">
                        <th class="text-center">Observaciones de Entrenamientos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($observations_assists as $assist)
                    <tr>
                        <td>&nbsp;<strong class="bold"><small>{{ $assist->observations }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
            @if($observations_skills->isNotEmpty())
            <table class="table-full detail detail-lines">
                <thead>
                    <tr class="tr-tit">
                        <th class="text-center">Observaciones de Competencias</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($observations_skills as $skill)
                    <tr>
                        <td>&nbsp;<strong class="bold">Fecha: {{$skill->created_at->format('Y-m-d')}}</strong> <small>{{ $skill->observation }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        @endforeach
</body>
</html>
