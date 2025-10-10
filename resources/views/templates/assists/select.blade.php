@php
$colorClass = " ";
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
