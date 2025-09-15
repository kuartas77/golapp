@php
$class = '';
switch($payment->$field){
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
$class = $deleted ? '': $class;
$amount = checkValueEnrollment($payment, $field, $inscription_amount);
$selected = config('variables.KEY_PAYMENTS_SELECT')[$payment->$field];
@endphp
<div class="text-center">

    <span class="badge {{$class}}" style="padding: 10px 2px; font-size: 10px; width: 90px; align-items: center;">

        <span class="payments_amount">{{$amount}}</span>
        <br/>
        <span class="text-wrap select">{{$selected}}</span>

        <input type="hidden" name="{{$field}}_amount"  value="{{$amount}}" class="payments_amount">
        <input type="hidden" name="{{$field}}"  value="{{$payment->$field}}" class="payments">
    </span>
</div>
