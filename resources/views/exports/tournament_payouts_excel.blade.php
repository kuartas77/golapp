<table>
    <thead>
    <tr>
        <th>Nombres</th>
        <th>Torneo</th>
        <th>Estado</th>
        <th>Valor</th>
    </tr>
    </thead>
    <tbody>
    @foreach($payments as $payment)
        <tr>
            <td>{{ $payment->unique_code }} {{ $payment->inscription->player->full_names }}</td>
            <td>{{ $tournament->name }}</td>
            <td>{{ getPay($payment->status) }}</td>
            <td>{{ $payment->value }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
