<table>
    @include('templates.assists.thead_excel', ['classDays' => $classDays])
    <tbody>
    @foreach($assists as $assist)
        @php
            $countAS = 0;
        @endphp
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{ $assist->inscription->player->full_names }}</td>
            <td>{{ $assist->inscription->category }}</td>
            <td>{{ $assist->inscription->player->phones}}
                - {{ $assist->inscription->player->mobile}}</td>
            @for ($index = 1; $index <= count($classDays); $index++)
                <td>
                    @php
                        $column = numbersToLetters($index);
                        $countAS += $assist->$column == 'as' ? 1 : 0;
                    @endphp
                    {{ $assist->$column == null ? '': $optionAssist[$assist->$column] }}
                </td>
            @endfor
            <td> {{percent($countAS, count($classDays))}}%</td>
        </tr>
    @endforeach
    @php
        $resultado = (40 - $count);
    @endphp
    @for ($i = 0; $i <= $resultado; $i++)
        <tr>
            <td>{{ $count++ }}</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            @for ($j = 1; $j <= count($classDays); $j++)
                <td>&nbsp;</td>
            @endfor
            <td>&nbsp;</td>
        </tr>
    @endfor
    </tbody>
</table>
<table>
    <tr>
        <td>ASISTENCIA:X</td>
        <td>FALTA:F</td>
        <td>EXCUSA:E</td>
        <td>RETIRO:R</td>
        <td>INCAPACIDAD:I</td>
    </tr>
</table>
