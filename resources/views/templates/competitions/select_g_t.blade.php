{!! html()->select($name, $values, $value)->attributes(['class' => 'form-control form-control-sm select', $value == 0 ? '' : 'required'])->placeholder('Selecciona...') !!}
