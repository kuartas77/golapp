@switch($value)
    @case(1)
        {!! Form::select($mes, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-success', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
    @break
    @case(2)
        {!! Form::select($mes, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments form-error', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
        @break
    @default
        {!! Form::select($mes, config('variables.KEY_PAYMENTS_SELECT'), $value,
        ['class' => 'form-control form-control-sm payments', 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}
        @break
@endswitch
