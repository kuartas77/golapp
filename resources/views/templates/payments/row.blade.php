<tr>
    <td>
        <small>{{$payment->year}}</small>
        <br>
        <a href="{{$payment->inscription->player->url_show}}" target="_blank">
            <small>{{ $payment->unique_code }}</small>
            <br>
            <small>{{ $payment->inscription->player->full_names }}</small>
            <input type="hidden" name="id" value="{{$payment->id}}">
        </a>
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'enrollment', 'value' => $payment->enrollment_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'enrollment', 'value' => $payment->enrollment, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'january', 'value' => $payment->january_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'january', 'value' => $payment->january, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'february', 'value' => $payment->february_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'february', 'value' => $payment->february, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'march', 'value' => $payment->march_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'march', 'value' => $payment->march, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'april', 'value' => $payment->april_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'april', 'value' => $payment->april, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'may', 'value' => $payment->may_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'may', 'value' => $payment->may, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'june', 'value' => $payment->june_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'june', 'value' => $payment->june, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'july', 'value' => $payment->july_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'july', 'value' => $payment->july, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'august', 'value' => $payment->august_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'august', 'value' => $payment->august, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'september', 'value' => $payment->september_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'september', 'value' => $payment->september, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'october', 'value' => $payment->october_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'october', 'value' => $payment->october, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'november', 'value' => $payment->november_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'november', 'value' => $payment->november, 'deleted' => $deleted])
    </td>
    <td>
        @include('templates.payments.input', ['mes' => 'december', 'value' => $payment->december_amount, 'deleted' => $deleted])
        @include('templates.payments.select', ['mes' => 'december', 'value' => $payment->december, 'deleted' => $deleted])
    </td>
</tr>
