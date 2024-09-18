<table>
    <thead>
    <tr>
        <th>Año</th>
        <th>Código Unico</th>
        <th>Nombres</th>
        <th>Categoría</th>
        <th>Matrícula</th>
        <th>Enero</th>
        <th>Febrero</th>
        <th>Marzo</th>
        <th>Abril</th>
        <th>Mayo</th>
        <th>Junio</th>
        <th>Julio</th>
        <th>Agosto</th>
        <th>Septiembre</th>
        <th>Octubre</th>
        <th>Noviembre</th>
        <th>Diciembre</th>
    </tr>
    </thead>
    <tbody>
    @foreach($payments as $payment)
        <tr>
            <td>{{$payment->year}}</td>
            <td>{{$payment->unique_code}}</td>
            <td>{{$payment->inscription->player->full_names}}</td>
            <td>{{$payment->category}}</td>
            @include('templates.payments.color',['amount' => getAmount($payment->enrollment_amount), 'value' => $payment->enrollment])
            @include('templates.payments.color',['amount' => getAmount($payment->january_amount), 'value' => $payment->january])
            @include('templates.payments.color',['amount' => getAmount($payment->february_amount), 'value' => $payment->february])
            @include('templates.payments.color',['amount' => getAmount($payment->march_amount), 'value' => $payment->march])
            @include('templates.payments.color',['amount' => getAmount($payment->april_amount), 'value' => $payment->april])
            @include('templates.payments.color',['amount' => getAmount($payment->may_amount), 'value' => $payment->may])
            @include('templates.payments.color',['amount' => getAmount($payment->june_amount), 'value' => $payment->june])
            @include('templates.payments.color',['amount' => getAmount($payment->july_amount), 'value' => $payment->july])
            @include('templates.payments.color',['amount' => getAmount($payment->august_amount), 'value' => $payment->august])
            @include('templates.payments.color',['amount' => getAmount($payment->september_amount), 'value' => $payment->september])
            @include('templates.payments.color',['amount' => getAmount($payment->october_amount), 'value' => $payment->october])
            @include('templates.payments.color',['amount' => getAmount($payment->november_amount), 'value' => $payment->november])
            @include('templates.payments.color',['amount' => getAmount($payment->december_amount), 'value' => $payment->december])
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="background:green; color: black;">Pagó</td>
        <td style="background:aqua; color: black;">Abonó</td>
        <td style="background:yellow;">Pagó - Efectivo</td>
        <td style="background:blue; color: white;">Pagó - Consignación</td>
        <td style="background:#572364; color: white;">Pago Anualidad Consignación</td>
        <td style="background:#6F4E37; color: white;">Pago Anualidad Efectivo</td>
        <td style="background:red; color: white;">Debe</td>
        <td style="background:orange; color: black;">Retiro Temporal</td>
        <td style="background:black; color: white;">Retiro Definitivo</td>
        <td style="background:#fac282; color: black;">Incapacidad</td>
        <td style="background:#009688; color: white;">Becado</td>
        <td>Acuerdo de pago</td>
        <td>Otros</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>Totales Por Tipo De Pago:</td>
        <td>{{$accumulate['pago']}}</td>
        <td>{{$accumulate['abono']}}</td>
        <td>{{$accumulate['pago_efectivo']}}</td>
        <td>{{$accumulate['pago_consignacion']}}</td>
        <td>{{$accumulate['pago_anual_consignacion']}}</td>
        <td>{{$accumulate['pago_anual_efectivo']}}</td>
        <td>{{$accumulate['debe']}}</td>
        <td>{{$accumulate['temporal']}}</td>
        <td>{{$accumulate['definitivo']}}</td>
        <td>{{$accumulate['incapacidad']}}</td>
        <td>{{$accumulate['becado']}}</td>
        <td>{{$accumulate['acuerdo']}}</td>
        <td>{{$accumulate['otros']}}</td>
    </tr>
    </tbody>
</table>
