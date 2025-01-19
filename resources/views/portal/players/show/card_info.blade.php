<div class="col-md-6">

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active show text-themecolor" data-toggle="tab" href="#info" role="tab">Información Básica</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-themecolor" data-toggle="tab" href="#family" role="tab">Información Familiar</a>
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
                                    <div class="col-md-4">
                                        <strong class="text-themecolor"><i class="fas fa-hospital-alt margin-r-5"></i> EPS</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->eps }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="text-themecolor"><i class="fa fa-phone margin-r-5"></i> Teléfonos</strong>
                                        <ul class="small-list">
                                            <li>Fijo: {{ $player->phones }}</li>
                                            <li>Movil: {{ $player->mobile }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="text-themecolor"><i class="fa fa-envelope margin-r-5"></i> Correo</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->email }}</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <strong class="text-themecolor"><i class="fa fa-book margin-r-5"></i> Instituto/Colegio/Escuela</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->school }}</li>
                                            <li>Grado: {{ $player->degree }}</li>
                                            <li>Jornada: {{ $player->jornada }}</li>
                                            <li>Seguro Estudiantil: {{ $player->student_insurance }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="text-themecolor"><i class="fa fa-map-marker margin-r-5"></i> Dirección</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->address }}</li>
                                            <li>{{ $player->neighborhood }}</li>
                                            <li>{{ $player->municipality }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="text-themecolor"><i class="fa fa-map-marker margin-r-5"></i> Antecedentes Médicos</strong>
                                        <ul class="small-list">
                                            <li>{{ $player->medical_history }}</li>
                                        </ul>
                                    </div>

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

    @if($player->inscriptions->isNotEmpty())
    <div class="card m-b-1">
        <div class="card-body">
            <div class="row no-gutters">
                @foreach($player->inscriptions as $inscription)

                <a class="btn btn-info m-1" href="{{route('portal.export.inscription', [$inscription->player_id, $inscription->id])}}" target="_blank" role="tab">Informe {{$inscription->year}}</a>

                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
