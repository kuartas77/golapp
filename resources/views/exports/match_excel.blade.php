<table>
    <tr >
        <td>Deportista</td>
        <td>Codigo</td>
        <td>Asistio</td>
        <td>Titular</td>
        <td>Jugó Aprox</td>
        <td>Posición</td>
        <td>Goles</td>
        <td>Amarillas</td>
        <td>Rojas</td>
        <td>Calificación</td>
        <td>Observación</td>
    </tr>
    @foreach($inscriptions as $inscription)
        @php
            $cantidad = $loop->count + 1;
        @endphp
        <tr class="tr-info">
            <td>{{ $inscription->player->full_names }}</td>
            <td>{{ $inscription->unique_code }}</td>
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
    @endforeach
    
    @php
        $resultado = (20 - $cantidad);
    @endphp
    @for ($i = 0; $i <= $resultado; $i++)
        <tr class="tr-info">
            <td class="texto">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    @endfor
</table>
