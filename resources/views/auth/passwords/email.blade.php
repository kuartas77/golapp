@extends('layouts.app')
@section('title', 'Recuperación De Contraseña')
@section('css')

@endsection
@section('content')
<div class="login-register">
    <div class="login-box card">
        <div class="card-body">

            <form method="POST" action="{{ route('password.email') }}" class="form-horizontal form-material" id="form-reset">
                @csrf
                <img src="{{asset('img/log3.jpg')}}" alt="{{config('app.name', 'Laravel')}}" class="img-center img-responsive">

                <div class="form-group">
                    <div class="col-xs-12">
                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required="true" autocomplete="off" placeholder="{{ __('E-Mail Address') }} registrado">
                        @if ($errors->has('email'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
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
                        <button class="btn btn-info btn-xs btn-block text-uppercase waves-effect waves-light" type="submit">{{ __('Send Password Reset Link') }}</button>
                    </div>
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
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
