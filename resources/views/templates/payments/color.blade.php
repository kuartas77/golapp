@switch($value)
    @case(1)
        <td style="background:green; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
    @break
    @case(2)
        <td style="background:red; color: white;" class="text-center"><small>{{getPay($value)}}</small></td>
        @break
    @default
        <td class="text-center"><small>{{getPay($value)}}</small></td>
    @break
@endswitch