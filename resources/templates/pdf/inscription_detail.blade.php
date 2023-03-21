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
                <td class=""><strong class="bold">Nombres:</strong> {{ $player->names }}</td>
                <td class=""><strong class="bold">Apellidos:</strong> {{ $player->last_names }}</td>
                <td class=""><strong class="bold">Doc. de Identidad:</strong> {{ $player->identification_document }}</td>
            </tr>
            <tr>
                @if($player->gender == 'M')
                    <td class=""><strong class="bold">Genero:</strong> Masculino</td>
                @else
                    <td class=""><strong class="bold">Genero:</strong> Femenino</td>
                @endif
                <td class=""><strong class="bold">Fecha de Nacimiento:</strong> {{ $player->date_birth }}</td>
                <td class=""><strong class="bold">Lugar de Nacimiento:</strong> {{ $player->place_birth }}</td>
            </tr>
            <tr>
                <td class=""><strong class="bold">Dirección:</strong> {{ $player->address }}</td>
                <td class=""><strong class="bold">Municipio:</strong> {{ $player->municipality }}</td>
                <td class=""><strong class="bold">Barrio:</strong> {{ $player->neighborhood }}</td>
            </tr>
            <tr>
                <td class=""><strong class="bold">Teléfonos:</strong> {{ $player->phones }} {{ $player->mobile }}</td>
                <td class=""><strong class="bold">Correo Electrónico:</strong> {{ $player->email }}</td>
                <td class=""><strong class="bold">EPS:</strong> {{ $player->eps }}</td>
            </tr>
            <tr>
                <td class="" colspan="2"><strong class="bold">Instituto/Colegio/Escuela:</strong> {{ $player->school }}</td>
                <td class=""><strong class="bold">Grado:</strong> {{ $player->degree }}</td>
            </tr>
        </tbody>
    </table>
    
        @foreach($player->inscriptions as $inscription)

            <table class="table-full detail detail-lines">
                <thead>
                    <tr class="tr-tit">
                        <th colspan="2" class="text-center">Estadisticas {{$quarter_text}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center"><strong class="bold">Total Partidos:</strong> {{ $inscription->format_average['total_matches'] }}</td>
                        <td class="text-center"><strong class="bold"># Asistencias A Partidos:</strong> {{ $inscription->format_average['assistance'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-center"><strong class="bold">Total Titular:</strong> {{ $inscription->format_average['titular'] }}</td>
                        <td class="text-center"><strong class="bold">Promedio De Calificación:</strong> {{ $inscription->format_average['qualification'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-center"><strong class="bold">Total De Goles:</strong> {{ $inscription->format_average['goals'] }}</td>
                        <td class="text-center"><strong class="bold">Promedio De Goles:</strong> {{ $inscription->format_average['goals_avg'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-center"><strong class="bold">Total Amarillas:</strong> {{ $inscription->format_average['yellow_cards'] }}</td>
                        <td class="text-center"><strong class="bold">Promedio Amarillas:</strong> {{ $inscription->format_average['yellow_cards_avg'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-center"><strong class="bold">Total Rojas:</strong> {{ $inscription->format_average['red_cards'] }}</td>
                        <td class="text-center"><strong class="bold">Promedio Rojas:</strong> {{ $inscription->format_average['red_cards_avg'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-center"><strong class="bold">Minutos Jugados:</strong> {{ $inscription->format_average['played_approx'] }}</td>
                        <td class="text-center"><strong class="bold">Promedio de Minutos Jugados:</strong> {{ $inscription->format_average['played_approx_avg'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong class="bold">Posiciones En el Campo:</strong> {{ $inscription->format_average['positions'] }}</td>
                    </tr>
                </tbody>        
            </table>

            @if($show_payments_assists)
            <table class="table-full detail detail-lines">
                <thead>
                <tr class="tr-tit">
                    <th colspan="16" class="text-center">Asistencias</th>
                </tr>
                <tr class="tr-tit">
                    <td class="text-center bold">Mes</td>
                    <td class="text-center bold">1</td>
                    <td class="text-center bold">2</td>
                    <td class="text-center bold">3</td>
                    <td class="text-center bold">4</td>
                    <td class="text-center bold">5</td>
                    <td class="text-center bold">6</td>
                    <td class="text-center bold">7</td>
                    <td class="text-center bold">8</td>
                    <td class="text-center bold">9</td>
                    <td class="text-center bold">10</td>
                    <td class="text-center bold">11</td>
                    <td class="text-center bold">12</td>
                    <td class="text-center bold">13</td>
                    <td class="text-center bold">14</td>
                    <td class="text-center bold">15</td>
                </tr>
                </thead>
                <tbody>
                @forelse ($inscription->assistance as $assistance)
                    <tr>
                        <td class="text-center bold">{{ $assistance->month }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_one) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_two) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_three) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_four) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_five) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_six) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_seven) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_eight) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_nine) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_ten) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_eleven) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_twelve) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_thirteen) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_fourteen) }}</td>
                        <td class="text-center bold">{{ checkAssists($assistance->assistance_fifteen) }}</td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="16" align="center">Sin Registros De Asistencia</th>
                    </tr>
                @endforelse
                </tbody>
            </table>
            
            <table class="table-full detail detail-lines">
                <tr>
                    <td class="text-center"><strong>ASISTENCIA:X</strong></td>
                    <td class="text-center"><strong>FALTA:F</strong></td>
                    <td class="text-center"><strong>EXCUSA:E</strong></td>
                    <td class="text-center"><strong>RETIRO:R</strong></td>
                    <td class="text-center"><strong>INCAPACIDAD:I</strong></td>
                </tr>
            </table>

            
            <table class="table-full detail detail-lines">
                <tbody>
                @forelse ($inscription->payments as $pay)
                    <tr class="tr-tit">
                        <th colspan="4" class="text-center">Mensualidades Año: {{$pay->year}}</th>
                    </tr>
                    <tr>
                        <td class="text-center bold">Enero: {{getPay($pay->january)}}</td>
                        <td class="text-center bold">Febrero: {{getPay($pay->february)}}</td>
                        <td class="text-center bold">Marzo: {{getPay($pay->march)}}</td>
                        <td class="text-center bold">Abril: {{getPay($pay->april)}}</td>
                    </tr>
                    <tr>
                        <td class="text-center bold">Mayo: {{getPay($pay->may)}}</td>
                        <td class="text-center bold">Junio: {{getPay($pay->june)}}</td>
                        <td class="text-center bold">Julio: {{getPay($pay->july)}}</td>
                        <td class="text-center bold">Agosto: {{getPay($pay->august)}}</td>
                    </tr>
                    <tr>
                        <td class="text-center bold">Septiembre: {{getPay($pay->september)}}</td>
                        <td class="text-center bold">Octubre: {{getPay($pay->october)}}</td>
                        <td class="text-center bold">Noviembre: {{getPay($pay->november)}}</td>
                        <td class="text-center bold">Diciembre: {{getPay($pay->december)}}</td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="4" class="text-center">Sin Registro de pagos.</th>
                    </tr>
                @endforelse
                </tbody>
            </table>
            @endif
        @endforeach
</body>
</html>
