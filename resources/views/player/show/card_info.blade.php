<div class="col-xl-9 col-lg-9 col-md-12 col-sm-12">

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active show" data-toggle="tab" href="#info" role="tab">Información Básica</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#family" role="tab">Información Familiar</a>
                </li>
                <li class="nav-item ml-auto card-actions">
                    <a class="nav-link" data-action="collapse" data-toggle="tooltip" data-placement="left" title="Click acá"><i class="ti-plus"></i></a>
                </li>
            </ul>

        </div>
        <div class="card-body collapse show">

            <div class="tab-content">
                <div class="tab-pane show active" id="info" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong><i class="fas fa-hospital-alt margin-r-5"></i> EPS</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->eps }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><i class="fa fa-phone margin-r-5"></i> Teléfonos</strong>
                                        <ul class="small-list">
                                            <li>Fijo: {{ $player->phones }}</li>
                                            <li>Movil: {{ $player->mobile }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><i class="fa fa-envelope margin-r-5"></i> Correo</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->email }}</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <strong><i class="fa fa-book margin-r-5"></i> Instituto/Colegio/Escuela</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->school }}</li>
                                            <li>Grado: {{ $player->degree }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><i class="fa fa-map-marker margin-r-5"></i> Dirección</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->address }}</li>
                                            <li>{{ $player->neighborhood }}</li>
                                            <li>{{ $player->municipality }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="family" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    @foreach($player->people as $people)
                                        <div class="col-md-3">
                                            <h5 class="text-center">{{$people->is_tutor ? '(Acudiente)' : ''}} {{\Illuminate\Support\Str::upper($people->relationship_name)}}</h5>
                                            <ul class="small-list">
                                                <li>Nombre: {{$people->names}}</li>
                                                <li>Cédula: {{$people->identification_card}}</li>
                                                <li>Teléfono: {{$people->phone}}</li>
                                                <li>Movil: {{$people->mobile}}</li>
                                                <li>Profesión {{$people->profession}}</li>
                                                <li>Empresa: {{$people->business}}</li>
                                                <li>Cargo: {{$people->position}}</li>
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    @foreach($player->inscriptions as $inscription)

        <div class="card m-b-0">
            <div class="card-header">

                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link disabled" href="javascript:void(0)">{{$inscription->year}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#average_{{$loop->index}}" role="tab">Estadisticas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pays_{{$loop->index}}" role="tab">Pagos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#asisstances_{{$loop->index}}" role="tab">Asistencias A
                            Entrenamientos</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-info" href="{{route('export.inscription', [$inscription->player_id, $inscription->id])}}" target="_blank" role="tab">Resumen {{$inscription->year}} PDF</a>
                    </li>

                    <li class="nav-item ml-auto card-actions">
                        <a class="nav-link" data-action="collapse" data-toggle="tooltip" data-placement="left" title="Click acá"><i class="ti-plus"></i></a>
                    </li>
                </ul>

            </div>
            <div class="card-body collapse show">
                <div class="tab-content">

                    <div class="tab-pane active" id="average_{{$loop->index}}" role="tabpanel">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="row">
                                        <ul>
                                            <li>Estarán habilitados el último mes del trimestre.</li>
                                            <li>Primer trimestre: 1 de enero hasta el 31 de marzo.</li>
                                            <li>Segundo trimestre: 1 de abril hasta el 30 de junio.</li>
                                            <li>Tercer trimestre: 1 de julio al 30 de septiembre.</li>
                                            <li>Cuarto trimestre: 1 de octubre al 31 de diciembre.</li>
                                        </ul>
                                    </div>
                                    <div class="row">
                                        @if(Carbon\Carbon::now()->month >= 3)                                   
                                        <div class="col-md-3">
                                            <a class="btn btn-info" href="{{route('export.inscription', [$inscription->player_id, $inscription->id, $inscription->year, 'quarter_one'])}}" target="_blank">PDF Primer trimestre</a>
                                        </div>
                                        @endif
                                        @if(Carbon\Carbon::now()->month >= 6)
                                        <div class="col-md-3">
                                            <a class="btn btn-info" href="{{route('export.inscription', [$inscription->player_id, $inscription->id, $inscription->year, 'quarter_two'])}}" target="_blank">PDF Segundo trimestre</a>
                                        </div>
                                        @endif
                                        @if(Carbon\Carbon::now()->month >= 9)
                                        <div class="col-md-3">
                                            <a class="btn btn-info" href="{{route('export.inscription', [$inscription->player_id, $inscription->id, $inscription->year, 'quarter_three'])}}" target="_blank">PDF Tercer trimestre</a>
                                        </div>
                                        @endif
                                        @if(Carbon\Carbon::now()->month >= 11)
                                        <div class="col-md-3">
                                            <a class="btn btn-info" href="{{route('export.inscription', [$inscription->player_id, $inscription->id, $inscription->year, 'quarter_four'])}}" target="_blank">PDF Cuarto trimestre</a>
                                        </div>
                                        @endif
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-3 col-xs-6 b-r"> Total Partidos
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['total_matches']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6 b-r"> Promedio De Calificación
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['qualification']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6 b-r"> # Asistencias A Partidos
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['assistance']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6 b-r"> Veces Titular
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['titular']}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6 b-r"> Minutos Jugados
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['played_approx']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6 b-r"> Promedio de Minutos Jugados
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['played_approx_avg']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6 b-r"> Total De Goles
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['goals']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6"> Promedio De Goles
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['goals_avg']}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6 b-r"> Total Rojas
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['red_cards']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6 b-r"> Promedio Rojas
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['red_cards_avg']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6 b-r"> Total Amarillas
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['yellow_cards']}}</p>
                                        </div>
                                        <div class="col-md-3 col-xs-6"> Promedio Amarillas
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['yellow_cards_avg']}}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6 b-r">
                                            <br>
                                            <p class="text-info text-center"></p>
                                        </div>
                                        <div class="col-md-6 col-xs-6 b-r"> Posiciones En el Campo
                                            <br>
                                            <p class="text-info text-center">{{$inscription->format_average['positions']}}</p>
                                        </div>

                                        <div class="col-md-3 col-xs-6 b-r">
                                            <br>
                                            <p class="text-info text-center"></p>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="pays_{{$loop->index}}" role="tabpanel">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <tbody>
                                        @forelse ($inscription->payments as $pay)
                                            <tr class="text-information">
                                                <th colspan="4" class="text-center">Mensualidades Año: {{$pay->year}}</th>
                                            </tr>
                                            <tr>
                                                <td>Enero: {{getPay($pay->january)}}</td>
                                                <td>Febrero: {{getPay($pay->february)}}</td>
                                                <td>Marzo: {{getPay($pay->march)}}</td>
                                                <td>Abril: {{getPay($pay->april)}}</td>
                                            </tr>
                                            <tr>
                                                <td>Mayo: {{getPay($pay->may)}}</td>
                                                <td>Junio: {{getPay($pay->june)}}</td>
                                                <td>Julio: {{getPay($pay->july)}}</td>
                                                <td>Agosto: {{getPay($pay->august)}}</td>
                                            </tr>
                                            <tr>
                                                <td>Septiembre: {{getPay($pay->september)}}</td>
                                                <td>Octubre: {{getPay($pay->october)}}</td>
                                                <td>Noviembre: {{getPay($pay->november)}}</td>
                                                <td>Diciembre: {{getPay($pay->december)}}</td>
                                            </tr>
                                        @empty
                                            <tr class="text-information">
                                                <th colspan="4" class="text-center">Sin Registro de pagos.</th>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="asisstances_{{$loop->index}}" role="tabpanel">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <td colspan="17" class="text-center">Asistencias</td>
                                        </tr>
                                        <tr class="tr-tit">
                                            <td>Mes</td>
                                            <td>1</td>
                                            <td>2</td>
                                            <td>3</td>
                                            <td>4</td>
                                            <td>5</td>
                                            <td>6</td>
                                            <td>7</td>
                                            <td>8</td>
                                            <td>9</td>
                                            <td>10</td>
                                            <td>11</td>
                                            <td>12</td>
                                            <td>13</td>
                                            <td>14</td>
                                            <td>15</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($inscription->assistance as $assistance)
                                            <tr>
                                                <td class="text-center">{{ $assistance->month }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_one) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_two) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_three) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_four) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_five) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_six) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_seven) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_eight) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_nine) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_ten) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_eleven) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_twelve) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_thirteen) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_fourteen) }}</td>
                                                <td class="text-center">{{ checkAssists($assistance->assistance_fifteen) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="18" align="center">Sin Registros De Asistencia</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                    <table class="table table-bordered">
                                        <tr class="text-information">
                                            <td class="text-center"><strong>ASISTENCIA:X</strong></td>
                                            <td class="text-center"><strong>FALTA:F</strong></td>
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

    @endforeach
</div>
