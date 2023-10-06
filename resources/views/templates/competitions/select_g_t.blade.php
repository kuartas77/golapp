{!! html()->select($name, $values, $value)->attributes(['class' => 'form-control form-control-sm select', $value == 0 ? '' : 'placeholder'=>'Selecciona...','required']) !!}
