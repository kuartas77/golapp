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

<h6><a
    href="javascript:void(0)"
    data-toggle='modal' data-target='#modal_attendance'
    class="badge {{$colorClass}} assist"
    data-id="{{$id}}"
    data-day="{{$classDay['day']}}"
    data-date="{{$classDay['date']}}"
    data-name="{{$classDay['name']}}"
    data-column="{{$classDay['column']}}"
    data-number="{{$classDay['number_class']}}"
    data-value="{{$value}}"
    data-observation="{{data_get($observations, $classDay['date'], '')}}"
    id="{{$id}}{{$classDay['day']}}"
    >{{$value == '' ? 'Seleccionar...': $optionAssist[$value]}}
    </a>
</h6>
