<table>
    <tr >
        <td>Deportista</td>
        <td>Codigo</td>
        <td>Asistio</td>
        <td>Titular</td>
        <td>Jugó Aprox</td>
        <td>Posición</td>
        <td>Goles</td>
        <td>Asistencia Gol</td>
        <td>Atajadas</td>
        <td>Amarillas</td>
        <td>Rojas</td>
        <td>Calificación</td>
    </tr>
    @foreach($inscriptions as $inscription)
        @php
            $cantidad = $loop->count + 1;
            $skillControl = $inscription instanceof \App\Models\SkillsControl ? $inscription : null;
            $player = $skillControl?->inscription?->player ?? $inscription->player;
            $uniqueCode = $skillControl?->inscription?->unique_code ?? $inscription->unique_code ?? $player?->unique_code;
            $booleanLabel = fn ($value) => ((int) $value) === 1 ? 'Sí' : 'No';
        @endphp
        <tr class="tr-info">
            <td>{{ $player?->full_names }}</td>
            <td>{{ $uniqueCode }}</td>
            <td>{{ $skillControl ? $booleanLabel($skillControl->assistance) : '' }}</td>
            <td>{{ $skillControl ? $booleanLabel($skillControl->titular) : '' }}</td>
            <td>{{ $skillControl?->played_approx }}</td>
            <td>{{ $skillControl?->position }}</td>
            <td>{{ $skillControl?->goals }}</td>
            <td>{{ $skillControl?->goal_assists }}</td>
            <td>{{ $skillControl?->goal_saves }}</td>
            <td>{{ $skillControl?->yellow_cards }}</td>
            <td>{{ $skillControl?->red_cards }}</td>
            <td>{{ $skillControl?->qualification }}</td>
        </tr>
    @endforeach

    @php
        $cantidad = ($cantidad ?? 1);
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
            <td>&nbsp;</td>
        </tr>
    @endfor
</table>
