<div class="col-md-4">
    @php
    $inscription = $player->inscriptions->first();
    @endphp
    @if($inscription)
    <div class="card m-b-1">
        <div class="card-header">

            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link text-themecolor disabled" href="javascript:void(0)">{{$inscription->year}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-themecolor" data-toggle="tab" href="#average" role="tab">Estadisticas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-themecolor" data-toggle="tab" href="#pays" role="tab">Pagos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-themecolor" data-toggle="tab" href="#asisstances" role="tab">Asistencias A
                        Entrenamientos</a>
                </li>
            </ul>

        </div>
        <div class="card-body collapse show">
            <div class="tab-content">

                <div class="tab-pane active" id="average" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive-md">
                                    <table class="table table-sm table-bordered">
                                        <tbody>

                                            <tr class="text-information">
                                                <th colspan="2" class="text-center">Estadisticas: {{$inscription->year}}</th>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Total Partidos </td>
                                                <td class="text-themecolor">{{$inscription->format_average['total_matches']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor"># Asistencias A Partidos</td>
                                                <td class="text-themecolor">{{$inscription->format_average['assistance']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor"># Veces Titular</td>
                                                <td class="text-themecolor">{{$inscription->format_average['titular']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Promedio De Calificación x Partido</td>
                                                <td class="text-themecolor">{{$inscription->format_average['qualification']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Minutos Jugados</td>
                                                <td class="text-themecolor">{{$inscription->format_average['played_approx']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Promedio de Minutos Jugados x Partido</td>
                                                <td class="text-themecolor">{{$inscription->format_average['played_approx_avg']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Total De Goles</td>
                                                <td class="text-themecolor">{{$inscription->format_average['goals']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Promedio De Goles x Partido</td>
                                                <td class="text-themecolor">{{$inscription->format_average['goals_avg']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Total Amarillas</td>
                                                <td class="text-themecolor">{{$inscription->format_average['yellow_cards']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Promedio Amarillas x Partido</td>
                                                <td class="text-themecolor">{{$inscription->format_average['yellow_cards_avg']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Total Rojas</td>
                                                <td class="text-themecolor">{{$inscription->format_average['red_cards']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Promedio Rojas x Partido</td>
                                                <td class="text-themecolor">{{$inscription->format_average['red_cards_avg']}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-themecolor">Posiciones En el Campo <small class="text-themecolor">{{$inscription->format_average['positions']}}</small></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="pays" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive-md">
                                    <table class="table table-sm table-bordered">
                                        <tbody>
                                            @forelse ($inscription->payments as $pay)
                                            <tr class="text-information">
                                                <th colspan="2" class="text-center">Mensualidades: {{$pay->year}}</th>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Enero</td>
                                                <td class="text-themecolor">{{getPay($pay->january)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Febrero</td>
                                                <td class="text-themecolor">{{getPay($pay->february)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Marzo</td>
                                                <td class="text-themecolor">{{getPay($pay->march)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Abril</td>
                                                <td class="text-themecolor">{{getPay($pay->april)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Mayo</td>
                                                <td class="text-themecolor">{{getPay($pay->may)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Junio</td>
                                                <td class="text-themecolor">{{getPay($pay->june)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Julio</td>
                                                <td class="text-themecolor">{{getPay($pay->july)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Agosto</td>
                                                <td class="text-themecolor">{{getPay($pay->august)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Septiembre</td>
                                                <td class="text-themecolor">{{getPay($pay->september)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Octubre</td>
                                                <td class="text-themecolor">{{getPay($pay->october)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Noviembre</td>
                                                <td class="text-themecolor">{{getPay($pay->november)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-themecolor">Diciembre</td>
                                                <td class="text-themecolor">{{getPay($pay->december)}}</td>
                                            </tr>
                                            @empty
                                            <tr class="text-information">
                                                <th colspan="2" class="text-center text-themecolor">Sin Registro de pagos.</th>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="asisstances" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive-md">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <td colspan="26" class="text-center">Asistencias: {{$inscription->year}}</td>
                                                <td class="text-center">% Asis.</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($inscription->assistance as $assistance)
                                            @php
                                            $totalSpan = 25;
                                            @endphp
                                            @php
                                            $countAS = 0;
                                            $classCount = $assistance->classDays->count();
                                            $colspan = ($totalSpan - $classCount);
                                            @endphp
                                            <tr>
                                                <td class="text-center text-themecolor">Clase #</td>
                                                @for ($index = 1; $index <= $classCount; $index++)
                                                    <td class="text-center text-themecolor">{{$index}}</td>
                                                    @endfor
                                                    <td colspan="{{ $colspan }}">&nbsp;</td>
                                                    <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-center text-themecolor">{{ $assistance->month }}</td>
                                                @for ($index = 1; $index <= $classCount; $index++)
                                                    @php
                                                    $column=numbersToLetters($index);
                                                    $countAS +=$assistance->$column == 'as' ? 1 : 0;
                                                    @endphp
                                                    <td class="text-center {{$assistance->$column == 'fa' ? 'error':''}}">
                                                        {!! $assistance->$column == null ? '': checkAssists($assistance->$column) !!}
                                                    </td>
                                                    @endfor
                                                    <td colspan="{{ $colspan }}">&nbsp;</td>
                                                    <td class="text-center {{ $countAS == 0 ? 'error' : '' }}">{{percent($countAS, $classCount)}}%</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="27" align="center">Sin Registros De Asistencia</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <table class="table table-sm table-bordered">
                                        <tr class="text-information">
                                            <td class="text-center success"><strong>ASISTENCIA:&#10003;</strong></td>
                                            <td class="text-center error"><strong>FALTA:&#10008;</strong></td>
                                            <td class="text-center"><strong>EXCUSA:E</strong></td>
                                            <td class="text-center"><strong>RETIRO:R</strong></td>
                                            <td class="text-center"><strong>INCAPACIDAD:I</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif
</div>