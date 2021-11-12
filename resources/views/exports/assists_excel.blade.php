<table>
    @include('templates.assists.thead_excel', ['classDays' => $classDays])
    <tbody>
    @foreach($assists as $assist)
        <tr>
            <td>{{ $assist->inscription->category }}</td>
            <td>{{ $assist->inscription->player->full_names }}</td>
            <td>{{ $assist->month }}</td>
            <td>{{ $assist->year }}</td>
            @for ($index = 1; $index <= $classDays->count(); $index++)
                <td>
                    @php
                        $column = numbersToLetters($index);
                    @endphp
                    {{ $assist->$column == null ? '': $optionAssist[$assist->$column] }}
                </td>
            @endfor
            <td>
                {{$assist->observation}}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
