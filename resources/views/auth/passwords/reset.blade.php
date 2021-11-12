@extends('layouts.app')
@section('title', __('Reset Password'))
@section('css')

@endsection
@section('content')
<div class="login-register" style="background-image:url('{{asset('imagenes/login-register.jpg')}}');" >
    <div class="login-box card">
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}" class="form-horizontal form-material" id="form-reset">
                @csrf
                <img src="{{asset('img/log3.jpg')}}" alt="{{config('app.name', 'Laravel')}}" class="img-center img-responsive">

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <div class="col-xs-12">
                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required="true" autocomplete="off" placeholder="{{ __('E-Mail Address') }} registrado" autofocus>
                        @if ($errors->has('email'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-12">
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required=true placeholder="{{ __('Password') }}">
                        @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-12">
                        <input id="password_confirmation" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password_confirmation" required=true placeholder="{{ __('Confirm Password') }}">
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex no-block align-items-center">
                        <div class="p-t-0">
                            <a href="{{route('login')}}" id="to-recover" class="text-muted"><i class="fa fa-lock m-r-5"></i> Ir al ingreso</a>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">{{ __('Reset Password') }}</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
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
            password:{required:"Ingresa la cédula registrada."},
            password_confirmation:{required:"Ingresa la cédula registrada."},
        },
        submitHandler : function (form) {
            form.submit();
        }
    });
</script>
@endsection
