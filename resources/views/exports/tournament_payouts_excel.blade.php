<table>
    <thead>
    <tr>
        <th>Nombres</th>
        <th>Torneo</th>
        <th>Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($payments as $payment)
        <tr>
            <td>{{ $payment->unique_code }} {{ $payment->inscription->player->full_names }}</td>
            <td>{{ $tournament->name }}</td>
            <td>{{ getPay($payment->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
