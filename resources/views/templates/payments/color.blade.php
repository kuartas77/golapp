@switch($value)
    @case(1)
        <td style="background:green; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(2)
        <td style="background:red; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
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