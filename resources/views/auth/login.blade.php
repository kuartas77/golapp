@extends('layouts.app')
@section('title', 'Bienvenid@s')
@section('css')

@endsection
@section('content')
    <div class="login-register">
        <div class="login-box card">
            <div class="card-body">

                <form method="POST" action="{{ route('login') }}" class="form-horizontal form-material"
                      id="form-ingreso">
                    @csrf
                    <img src="{{asset('img/log3.jpg')}}" alt="{{config('app.name', 'Laravel')}}"
                         class="img-center img-responsive" width="300px" height="300px">
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                   id="email" name="email" placeholder="Correo" value="{{ old('email') }}"
                                   required="true" autofocus="true">
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="password"
                                   class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" id="password"
                                   name="password" placeholder="Contraseña" required="true" autocomplete="off">
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex no-block align-items-center">
                            <div class="p-t-0">
                                <a href="{{route('password.request')}}" id="to-recover" class="text-muted"><i
                                        class="fa fa-lock m-r-5"></i> Recuperar contrase&ntilde;a</a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light"
                                    type="submit">Iniciar Sesión
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $("#form-ingreso").validate({
            rules: {
                email: {required: true, minlength: 5},
                password: {required: true}
            },
            messages: {
                email: {required: "Ingresa un correo."},
                password: {required: "Ingresa una contraseña"}
            },
            submitHandler: function (form) {
                form.submit();
            }
        });
    </script>
@endsection
