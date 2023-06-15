@switch($value)
    @case('as')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist form-success', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('fa')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist form-error', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('ex')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist form-orange', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('re')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist form-grey', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('in')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist form-warning', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @default
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
@endswitch