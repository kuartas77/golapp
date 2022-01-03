@extends('layouts.app')
@section('title', 'Perfil')
@section('content')
<x-bread-crumb title="Perfil {{$profile->user->name}}" :option="0" />
<x-row-card col-inside="8" col-outside="2">
    <a class="btn btn-info" href="{!! route('profiles.edit', [$profile->user->id]) !!}">Modificar Perfil</a>
    <hr class="m-t-0 m-b-40">
    <form class="form-horizontal" role="form">
        <div class="form-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Nombre:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->user->name}} </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Correo:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->user->email}} </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Genero:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->gender}} </p>
                        </div>
                    </div>
                </div>
                <!--/span-->
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Fecha De
                            Nacimiento:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->date_birth}} </p>
                        </div>
                    </div>
                </div>
                <!--/span-->
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Número Documento:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->identification_document}} </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Dirección:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{$profile->address}}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Teléfono:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->phone}} </p>
                        </div>
                    </div>
                </div>
                <!--/span-->
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Celular:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->mobile}} </p>
                        </div>
                    </div>
                </div>
                <!--/span-->
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Cargo:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->position}} </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Estudios:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->studies}} </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Contactos:</label>
                        <div class="col-md-8">
                            <p class="form-control-static"> {{$profile->contacts}} </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Experiencia:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{$profile->experience}}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Aptitudes:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{$profile->aptitude}}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="control-label text-right col-md-4">Referencias:</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{$profile->references}}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</x-row-card>
@endsection
@section('modals')
@endsection
@section('scripts')
<script>

</script>
@endsection