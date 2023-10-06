@php
$class = '';
switch($value){
    case 1:
        $class = 'color-success';
    break;
    case 2:
        $class = 'color-error';
    break;
    case 3:
        $class = 'color-agua';
    break;
    case 4:
        $class = 'color-incapacidad';
    break;
    case 5:
        $class = 'color-orange';
    break;
    case 6:
        $class = 'color-grey';
    break;
    case 8:
        $class = 'color-becado';
    break;
    case 9:
        $class = 'color-warning';
    break;
    case 10:
        $class = 'color-info';
    break;
    case 11:
        $class = 'color-purple';
    break;
    case 12:
        $class = 'color-brown';
    break;
    default:
        $class = '';
    break;
}
@endphp
{!! html()->select($mes,
    config('variables.KEY_PAYMENTS_SELECT'),
    $value)
    ->attributes(['class' => "form-control form-control-sm payments {$class}", 'placeholder'=>'Selecciona...', ($deleted || isInstructor()) ? 'disabled' : '']) !!}