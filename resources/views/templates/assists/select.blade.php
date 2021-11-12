{!! Form::select($column, $optionAssist, $value,  ['class' => 'assist',
'placeholder'=>'Selecciona...', $deleted ? 'disabled' : '' ]) !!}
{{--{!! Form::select($column, $optionAssist, $value,  ['class' => 'form-control form-control-sm assist',
'placeholder'=>'Selecciona...', !is_null($value) ? 'disabled' : '' ]) !!}--}}
