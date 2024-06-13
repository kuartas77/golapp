@php
$colorClass = " ";
switch($value){
    case 'as':
        $colorClass = $deleted ? '' : "color-success";
        break;
    case 'fa':
        $colorClass = $deleted ? '' : "color-error";
        break;
    case 'ex':
        $colorClass = $deleted ? '' : "color-orange";
        break;
    case 're':
        $colorClass = $deleted ? '' : "color-grey";
        break;
    case 'in':
        $colorClass = $deleted ? '' : "color-warning";
        break;
    default:
        $colorClass = "";
}
$colorClass = $deleted ? '': $colorClass;
@endphp

{!! html()->select($column, $optionAssist, $value)->attributes(['class' => "form-control form-control-sm assist $colorClass",  $deleted ? 'disabled' : '' ])->placeholder('Selecciona...') !!}
