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
                    <h4 class="card-title text-center text-themecolor m-t-5">Ingreso de Deportistas y/o Acudientes</h4>
                    <form method="POST" action="{{ route('portal.player.login') }}" class="form-horizontal form-material"
                        id="form-ingreso">
                        <img src="{{asset('img/light.png')}}" alt="{{config('app.name', 'Laravel')}}" class="img-center img-responsive" width="300px" height="300px">
                        @csrf
                        {!! RecaptchaV3::field('login') !!}
                        <div class="form-group ">
                            <div class="col-md-12">
                                <input type="text" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                                    id="username" name="username" placeholder="Documento ID" value="{{ old('username') }}"
                                    required="true" autofocus="true">
                                @if ($errors->has('username'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="password"
                                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" id="password"
                                    name="password" placeholder="Código Unico" required="true" autocomplete="off">
                                @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                                @endif
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
            username: {
                required: true
            },
            password: {
                required: true
            }
        },
        messages: {
            username: {
                required: "Ingresa el documento de identificación."
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