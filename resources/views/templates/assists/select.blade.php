@switch($value)
    @case('as')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-success',  $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
    @break
    @case('fa')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-error',  $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
    @break
    @case('ex')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-orange',  $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
    @break
    @case('re')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-grey', $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
    @break
    @case('in')
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist color-warning',  $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
    @break
    @default
        {!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist',  $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
    @break
@endswitch
