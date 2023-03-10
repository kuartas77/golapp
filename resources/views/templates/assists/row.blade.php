<tr>
    <input type='hidden' name='id' value="{{$assist->id}}">
    <td class="text-center">
        <a href="{{$assist->inscription->player->url_show}}" target="_blank">
            <small>{{ $assist->inscription->category }}</small>
            <br>
            <img class='media-object img-rounded' src='{{$assist->inscription->player->photo_url}}' width='60' height='60'>
            <br>
            <small>{{$assist->inscription->player->full_names}}</small>
        </a>
    </td>
    <td>{{$assist->month}}</td>
    @for ($index = 1; $index <= $classDays; $index++)
        <td>
            @php
                $column = numbersToLetters($index);
            @endphp
            @include('templates.assists.select', [
                'index' => $index ,
                'value' => $assist->$column,
                'column' => $column,
                'deleted' => $deleted
            ])
        </td>
    @endfor
    <td>
        @if(!$deleted)
            <button type='button' class='btn btn-primary observation' data-toggle='modal' data-target='#modal_observation'
                    data-id="{{$assist->id}}">Observación
            </button>
        @endif
    </td>
</tr>


