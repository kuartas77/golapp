{!! Form::select($mes, config('variables.KEY_PAYMENTS_SELECT'), $value,
['class' => 'form-control form-control-sm payments', 'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '']) !!}
