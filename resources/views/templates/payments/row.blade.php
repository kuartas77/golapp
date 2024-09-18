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
    @foreach($nameFields as $field)
    <td>
        @include('templates.payments.input', ['mes' => $field, 'value' => checkValueEnrollment($payment, $field, $inscription_amount), 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => $field, 'value' => $payment->$field, 'deleted' => $deleted, 'id' => $payment->id , 'iteration' => $loop->iteration])
    </td>
    @endforeach
</tr>
