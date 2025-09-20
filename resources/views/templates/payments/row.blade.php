<tr>
    <td class="text-center">
        <small>{{$payment->year}}</small>
        <br>
        <a href="{{$payment->inscription->player->url_show}}" target="_blank">
            <small>{{ $payment->unique_code }}</small>
            <br>
            <small>{{ $payment->inscription->player->full_names }}</small>
            <input type="hidden" name="id" value="{{$payment->id}}">
        </a>
    </td>
    <td class="text-center"><small>{{$payment->category}}</small></td>
    @if(isInstructor() || !$enabledPaymentOld)
        @foreach($nameFields as $field)
         <td>
            @include('templates.payments.badge')
        </td>
        @endforeach
    @else
        @foreach($nameFields as $field)
        <td>
            @include('templates.payments.input', ['mes' => $field, 'value' => checkValueEnrollment($payment, $field, $inscription_amount), 'deleted' => $deleted, 'isdeleted' => isset($payment->deleted_at)])
            @include('templates.payments.select', ['mes' => $field, 'value' => $payment->$field, 'deleted' => $deleted, 'id' => $payment->id , 'iteration' => $loop->iteration, 'isdeleted' => isset($payment->deleted_at)])
        </td>
        @endforeach
    @endif
</tr>
