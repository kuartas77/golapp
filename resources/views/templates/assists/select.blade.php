@switch($value)
    @case('as')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-success', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('fa')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-error', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('ex')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-orange', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('re')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-grey', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @case('in')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-warning', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
    @default
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
    @break
@endswitch