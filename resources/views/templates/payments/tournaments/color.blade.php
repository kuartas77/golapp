@switch($value)
    @case(1)
        <td style="background:green; color: black;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(2)
        <td style="background:red; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(3)
        <td style="background:aqua; color: black;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case("4")
        <td style="background:#fac282; color: black;" class="text-center"><small>{{(getPay($value))}}</small></td>
    @break
    @case(5)
        <td style="background:orange; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(6)
        <td style="background:grey; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case("8")
        <td style="background:#009688; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(9)
        <td style="background:yellow;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(10)
        <td style="background:blue; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(11)
        <td style="background:#572364; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(12)
        <td style="background:#6F4E37; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @default
        <td class="text-center"><small>{{getPay($value)}}</small></td>
    @break
@endswitch