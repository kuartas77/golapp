<tr>
    <td>{{$payment->year}}</td>
    <td bgcolor="{{$payment->inscription->scholarship ? 'green': ''}}{{$payment->check_payments >= 1 ?'red': ''}}"><small>{{$payment->inscription->player->full_names}}</small></td>
    <td><input type="hidden" name="id" value="{{$payment->id}}">{{$payment->unique_code}}</td>
    <td>@include('templates.payments.select', ['mes' => 'january', 'value' => $payment->january, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'february', 'value' => $payment->february, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'march', 'value' => $payment->march, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'april', 'value' => $payment->april, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'may', 'value' => $payment->may, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'june', 'value' => $payment->june, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'july', 'value' => $payment->july, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'august', 'value' => $payment->august, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'september', 'value' => $payment->september, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'october', 'value' => $payment->october, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'november', 'value' => $payment->november, 'deleted' => $deleted])</td>
    <td>@include('templates.payments.select', ['mes' => 'december', 'value' => $payment->december, 'deleted' => $deleted])</td>
</tr>