@extends('layouts.portal.public')
@section('title', 'Bienvenid@s')
@section('css')

@endsection
@section('content')
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="login-public">

            <div class="login-box card">
                <div class="card-body">
                    <h4 class="card-title text-center text-themecolor m-t-5">Ingreso de Acudientes</h4>
                    <p class="text-center text-muted m-b-20">Usa el correo del acudiente principal y tu contraseña personal.</p>
                    <form method="POST" action="{{ route('portal.guardian.login') }}" class="form-horizontal form-material"
                        id="form-ingreso">
                        <img src="{{asset('img/light.png')}}" alt="{{config('app.name', 'Laravel')}}" class="img-center img-responsive" width="300px" height="300px">
                        @csrf
                        {!! RecaptchaV3::field('guardian_login') !!}
                        @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                        @endif
                        <div class="form-group ">
                            <div class="col-md-12">
                                <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                    id="email" name="email" placeholder="Correo electrónico" value="{{ old('email', request('email')) }}"
                                    required="true" autofocus="true">
                                @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="password"
                                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" id="password"
                                    name="password" placeholder="Contraseña" required="true" autocomplete="off">
                                @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12 text-right">
                                <a href="{{ route('portal.password.request') }}" class="text-muted">¿Olvidaste tu contraseña?</a>
                            </div>
                        </div>

                        <div class="form-group text-center m-t-20">
                            <div class="col-md-12">
                                <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light"
                                    type="submit">Ingresar
                                </button>
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
{!! RecaptchaV3::initJs() !!}
@section('scripts')
<script type="text/javascript">
    $("#form-ingreso").validate({
        rules: {
            email: {
                required: true
            },
            password: {
                required: true
            }
        },
        messages: {
            email: {
                required: "Ingresa el correo electrónico registrado."
            },
            password: {
                required: "Ingresa la contraseña."
            }
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
</script>
@endsection
