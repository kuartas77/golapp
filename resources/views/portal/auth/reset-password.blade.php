@extends('layouts.portal.public')
@section('title', 'Restablecer acceso')
@section('content')
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="login-public">
            <div class="login-box card">
                <div class="card-body">
                    <h4 class="card-title text-center text-themecolor m-t-5">Definir nueva contraseña</h4>
                    <p class="text-center text-muted m-b-20">Crea una contraseña personal para ingresar al portal de acudientes.</p>

                    <form method="POST" action="{{ route('portal.password.update') }}" class="form-horizontal form-material" id="form-reset">
                        @csrf
                        <img src="{{asset('img/light.png')}}" alt="{{config('app.name', 'Laravel')}}" class="img-center img-responsive" width="300px" height="300px">

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <div class="col-md-12">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required="true" autocomplete="off" placeholder="Correo electrónico registrado" autofocus>
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required="true" placeholder="Contraseña nueva">
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <input id="password_confirmation" type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" name="password_confirmation" required="true" placeholder="Confirmar contraseña">
                                @if ($errors->has('password_confirmation'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
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
                                <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Guardar contraseña</button>
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
            password:{required:true},
            password_confirmation:{required:true}
        },
        messages:{
            email:{required:"Ingresa el correo electrónico registrado."},
            password:{required:"Ingresa la nueva contraseña."},
            password_confirmation:{required:"Confirma la nueva contraseña."},
        },
        submitHandler : function (form) {
            form.submit();
        }
    });
</script>
@endsection
