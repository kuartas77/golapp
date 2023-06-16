@switch($value)
    @case(1)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-success', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(2)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-error', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(5)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-orange', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(6)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-grey', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(9)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-warning', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(10)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-info', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(11)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-purple', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(12)
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-brown', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @default
        {!! Form::select($name, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
@endswitch
