@extends('layouts.app')
@section('title', 'Renovación Contraseña')
@section('css')

@endsection
@section('content')
<div class="login-register" style="background-image:url('{{asset('imagenes/login-register.jpg')}}');" >
    <div class="login-box card">
        <div class="card-body">

            <form method="POST" action="{{ route('expired') }}" class="form-horizontal form-material" id="form-ingreso">
                @csrf
                <img src="{{asset('imagenes/log3.png')}}" alt="Aserpublicos" class="img-center img-responsive">
                <blockquote class="blockquote text-center">
                    <p class="text-justify">{{Lang::getFromJson("Your Password is expired, You need to change your password.")}}</p>
                </blockquote>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input type="password" class="form-control{{ $errors->has('password_old') ? ' is-invalid' : '' }}" id="password_old" name="password_old" placeholder="Contraseña actual" required="true" autocomplete="off">
                        @if ($errors->has('password_old'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password_old') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" placeholder="Contraseña nueva" required="true" autocomplete="off">
                        @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmar contraseña nueva" required="true" autocomplete="off">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-12">
                        @if ($errors->has('error'))
                        <span class="help-block">
                            <strong>{{ $errors->first('error') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-success btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Cambiar Contraseña</button>
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
        rules:{
            password:{required:true},
            // "g-recaptcha-response":{required:true}
        },
        messages:{

            password:{required:"Ingresa una contraseña"}
        },
        submitHandler : function (form) {
            form.submit();
        }
    });
</script>
@endsection
