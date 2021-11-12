<div class="col-lg-9 col-xlg-9 col-md-3">
    <div class="card">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs profile-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#average" role="tab">Estadisticas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#pays" role="tab">Pagos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#asisstances" role="tab">Asistencias A Entrenamientos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#info" role="tab">Información Básica</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#family" role="tab">Información Familiar</a>
            </li>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">

            <div class="tab-pane active" id="average" role="tabpanel">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            @if($inscription->average->isNotEmpty())
                                <div class="row">
                                    <div class="col-md-3 col-xs-6 b-r"> Total Partidos
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['total_matches']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r"> Promedio De Calificación
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['qualification']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r"> # Asistencias A Partidos
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['assistance']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r"> Veces Titular
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['titular']}}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-3 col-xs-6 b-r"> Minutos Jugados
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['played_approx']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r"> Promedio de Minutos Jugados
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['played_approx_avg']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r"> Total De Goles
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['goals']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6"> Promedio De Goles
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['goals_avg']}}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-3 col-xs-6 b-r"> Total Rojas
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['red_cards']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r"> Promedio Rojas
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['red_cards_avg']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r"> Total Amarillas
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['yellow_cards']}}</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6"> Promedio Amarillas
                                        <br>
                                        <p class="text-info text-center">{{$inscription->average['yellow_cards_avg']}}</p>
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
                                        <p class="text-info text-center">{{$inscription->average['positions']}}</p>
                                    </div>

                                    <div class="col-md-3 col-xs-6 b-r">
                                        <br>
                                        <p class="text-info text-center"></p>
                                    </div>
                                </div>
                                <hr>
                            @else
                                <div class="row">
                                    <div class="col-md-3 col-xs-6 b-r">
                                        <br>
                                        <p class="text-info"></p>
                                    </div>
                                    <div class="col-md-6 col-xs-6 b-r">
                                        <br>
                                        <p class="text-warning text-center">No Se Encontraron Registros De
                                            Competencias.</p>
                                    </div>
                                    <div class="col-md-3 col-xs-6 b-r">
                                        <br>
                                        <p class="text-info"></p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="pays" role="tabpanel">
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
            <!--second tab-->
            <div class="tab-pane" id="asisstances" role="tabpanel">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td colspan="17" class="text-center">Asistencias</td>
                                </tr>
                                <tr class="tr-tit">
                                    <td>Año</td>
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
                                @forelse ($inscription->todo_assistance as $assistance)
                                    <tr>
                                        <td class="text-center">{{ $assistance->year }}</td>
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

            <div class="tab-pane" id="info" role="tabpanel">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-hospital-alt margin-r-5"></i> EPS</strong>
                                    <ul class="">
                                        <li>{{ $inscription->eps }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fa fa-phone margin-r-5"></i> Teléfonos</strong>
                                    <ul class="">
                                        <li>Fijo: {{ $inscription->phones }}</li>
                                        <li>Movil: {{ $inscription->mobile }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fa fa-envelope margin-r-5"></i> Correo</strong>
                                    <ul class="">
                                        <li>{{ $inscription->email }}</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <strong><i class="fa fa-book margin-r-5"></i> Instituto/Colegio/Escuela</strong>
                                    <ul class="">
                                        <li>{{ $inscription->school }}</li>
                                        <li>Grado: {{ $inscription->degree }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fa fa-map-marker margin-r-5"></i> Dirección</strong>
                                    <ul class="">
                                        <li>{{ $inscription->address }}</li>
                                        <li>{{ $inscription->neighborhood }}</li>
                                        <li>{{ $inscription->municipality }}</li>
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
                                @foreach($inscription->peoples as $people)
                                    <div class="col-md-4">
                                        <h3 class="text-center">{{$people->is_tutor ? '(Acudiente)' : ''}} {{\Illuminate\Support\Str::upper($people->relationship_name)}}</h3>
                                        <ul>
                                            <li><strong>Nombre:</strong> {{$people->names}}</li>
                                            <li><strong>Cédula:</strong> {{$people->identification_card}}</li>
                                            <li><strong>Teléfono:</strong> {{$people->phone}}</li>
                                            <li><strong>Movil:</strong> {{$people->mobile}}</li>
                                            <li><strong>Profesión:</strong> {{$people->profession}}</li>
                                            <li><strong>Empresa:</strong> {{$people->business}}</li>
                                            <li><strong>Cargo:</strong> {{$people->position}}</li>
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
