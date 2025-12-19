<div class="col-md-7">

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active show" data-toggle="tab" href="#info" role="tab">Inscripciones</a>
                </li>
                @if($school->tutor_platform)
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#platform-guardian" role="tab">Plataforma Acudientes</a>
                </li>
                @endif
            </ul>
        </div>
        <div class="card-body collapse show">

            <div class="tab-content">
                <div class="tab-pane active show" id="info" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="text-center m-2">
                                        <h2><strong>{{$school->name}}</strong> Formulario de inscripción {{$year}}</h2>
                                        @if($school->inscriptions_enabled)
                                            @if($school->send_documents)
                                            <p class="lead">Recuerda tener listo de forma digital la siguiente documentación:</p>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">Se solicitará la firma del acudiente y del deportista</li>
                                                <li class="list-group-item">Documento de identidad del deportista</li>
                                                <li class="list-group-item">Foto del deportista</li>
                                                <li class="list-group-item">Certificado eps</li>
                                                <li class="list-group-item">El documento de identidad del acudiente</li>
                                                <li class="list-group-item">Recibo de pago de la inscripción</li>
                                            </ul>
                                            <p class="lead">Una vez finalizado el proceso, se revisarán los archivos proporcionados y se enviará una notificación del estado de la inscripción.</p>
                                            @else
                                            <p class="lead">Con la información proporcionada al momento de la inscripción, la escuela gestionará el resto del proceso.</p>
                                            @endif
                                        <a class="btn waves-effect waves-light btn-rounded btn-info"
                                            href="javascript:void(0)" data-toggle="modal" data-target="#modal_inscription" data-backdrop="static" data-keyboard="false">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                            Realizar Inscripción
                                        </a>
                                        @else
                                        <h3 class="lead">Las inscripciones se encuentran deshabilitadas comunicate con {{$school->name}}</h3>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="platform-guardian" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="text-center m-2">
                                        <h2><strong>{{$school->name}}</strong> Plataforma de acudientes</h2>
                                        <p class="lead">Ingresando a la plataforma de acudientes podras realizar las siguientes acciones:</p>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">Ver y actualizar la información del deportista.</li>
                                            <li class="list-group-item">Ver el estado de los pagos realizados en los meses del año.</li>
                                            <li class="list-group-item">Ver el estado de las asistencias a entrenamientos del deportista.</li>
                                            <li class="list-group-item">Ver las estadísticas generadas en las competencias.</li>
                                            <li class="list-group-item">Historial por año de inscripción.</li>
                                        </ul>
                                        <p class="lead">Para el ingreso debes proporcionar el documento de identidad del deportista y su código único que fue enviado al correo del acudiente en el momento de la inscripción.</p>
                                        <a class="btn waves-effect waves-light btn-rounded btn-info" href="{{route('portal.login.form')}}">
                                            Ir a plataforma de acudientes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


</div>