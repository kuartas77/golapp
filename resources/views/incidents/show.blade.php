@extends('layouts.app')
@section('title', 'Incidencias')
@section('content')
    <x-bread-crumb title="Incidencias" :option="0"/>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body m-l-30 m-r-30">

                    <div class="row m-t-10">
                        <div class="col-md-6 col-xs-6 b-l b-r"> <strong>Nombre</strong>
                            <br>
                            <p class="text-muted">{{$professor->name}}</p>
                        </div>
                        <div class="col-md-6 col-xs-6 b-r"> <strong>Correo</strong>
                            <br>
                            <p class="text-muted">{{$professor->email}}</p>
                        </div>
                    </div>

                    <div class="row">
                        <ul class="list-group">
                            @foreach($incidents as $incident)
                                <li class="list-group-item">
                                    <h5 class="font-weight-bold text-uppercase">Titulo: {{$incident->incidence}}</h5>
                                    <p class="text-justify text-capitalize"><strong class="font-weight-bold">Descripción:</strong> {{$incident->description}}</p>
                                    <small>Fecha: {{$incident->created_at->format('Y-m-d h:i:s a')}}</small>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script></script>
@endsection
