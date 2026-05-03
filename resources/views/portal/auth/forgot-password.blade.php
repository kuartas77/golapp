@extends('layouts.portal.public')
@section('title', 'Recuperar acceso')
@section('content')
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="login-public">
            <div class="login-box card">
                <div class="card-body">
                    <h4 class="card-title text-center text-themecolor m-t-5">Recuperar acceso de acudiente</h4>
                    <p class="text-center text-muted m-b-20">Te enviaremos un enlace para definir o restablecer tu contraseña.</p>

                    <form method="POST" action="{{ route('portal.password.email') }}" class="form-horizontal form-material" id="form-reset">
                        @csrf
                        <img src="{{asset('img/light.png')}}" alt="{{config('app.name', 'Laravel')}}" class="img-center img-responsive" width="300px" height="300px">

                        @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                        @endif

                        <div class="form-group">
                            <div class="col-md-12">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required="true" autocomplete="off" placeholder="Correo electrónico registrado">
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="d-flex no-block align-items-center justify-content-end">
                                <a href="{{route('portal.login.form')}}" id="to-recover" class="text-muted"><i class="fa fa-lock m-r-5"></i> Volver al ingreso</a>
                            </div>
                        </div>

                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                                <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Enviar instrucciones</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    $("#form-reset").validate({
        rules:{
            email:{required:true},
        },
        messages:{
            email:{required:"Ingresa el correo electrónico registrado."}
        },
        submitHandler : function (form) {
            form.submit();
        }
    });
</script>
@endsection
