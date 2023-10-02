@switch($value)
    @case('as')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist color-success', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('fa')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist color-error', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('ex')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist color-orange', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('re')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist color-grey', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('in')
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist color-warning', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @default
        {!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
@endswitch