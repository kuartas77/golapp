@php
$class = '';
switch($value){
    case 1:
        $class = 'form-success';
    break;
    case 2:
        $class = 'form-error';
    break;
    case 3:
        $class = 'form-agua';
    break;
    case 5:
        $class = 'form-orange';
    break;
    case 6:
        $class = 'form-grey';
    break;
    case 9:
        $class = 'form-warning';
    break;
    case 10:
        $class = 'form-info';
    break;
    case 11:
        $class = 'form-purple';
    break;
    case 12:
        $class = 'form-brown';
    break;
    default:
        $class = '';
    break;
}
@endphp
{!! Form::select($name, 
    config('variables.KEY_PAYMENTS_SELECT'), 
    $value,
    ['class' => "form-control form-control-sm payments {$class}", 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '', 'style'=>"width: 25%;"]) !!}
