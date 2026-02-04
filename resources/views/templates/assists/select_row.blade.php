@php
$colorClass = "";

switch($value){
    case 1:
        $colorClass = $deleted ? '' : "color-success";
        break;
    case 2:
        $colorClass = $deleted ? '' : "color-error";
        break;
    case 3:
        $colorClass = $deleted ? '' : "color-orange";
        break;
    case 4:
        $colorClass = $deleted ? '' : "color-grey";
        break;
    case 5:
        $colorClass = $deleted ? '' : "color-warning";
        break;
    default:
        $colorClass = "";
}
@endphp
{!! html()->select($column, $optionAssist, $value)->attributes(['class' => 'form-control form-control-sm assist '.$colorClass,  $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
