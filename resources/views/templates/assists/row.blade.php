<tr>
    <input type='hidden' name='id' value="{{$assist->id}}">
    <td class="text-center">
        <a href="{{$assist->inscription->player->url_show}}" target="_blank">
            <small>{{ $assist->inscription->category }}</small>
            <br>
            <img class='media-object img-rounded' src='{{$assist->inscription->player->photo_url}}' width='90' height='60'>
            <br>
            <small>{{$assist->inscription->player->full_names}}</small>
        </a>
    </td>
    @if(is_null($column))
    <td>{{$assist->month}}</td>
    @for ($index = 1; $index <= $classDays->count(); $index++)
        <td class="text-center">
            @php
            $column = numbersToLetters($index);
            @endphp
            @include('templates.assists.select', [
                'id' => $assist->id,
                'index' => $index,
                'value' => $assist->$column,
                'column' => $column,
                'deleted' => $deleted,
                'observations' => $assist->observations,
                'classDay' => $classDays->firstWhere('column', $column)
            ])
        </td>
        @endfor
        @else
        <td class="text-center">
            @include('templates.assists.select', [
                'id' => $assist->id,
                'index' => 0,
                'value' => $assist->$column,
                'column' => $column,
                'deleted' => $deleted,
                'observations' => $assist->observations,
                'classDay' => $classDays->firstWhere('column', $column)
            ])
        </td>
        @endif
        <td class="text-center">
            @if(!$deleted)
            <button type='button' class='btn btn-primary observation' data-toggle='modal' data-target='#modal_observation'
                data-id="{{$assist->id}}">Ver observaciónes
            </button>
            @endif
        </td>
</tr>