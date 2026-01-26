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
        <td class="text-center">
            @php
            $column = numbersToLetters($index);
            @endphp
            @include('templates.assists.select_row', [
                'index' => $index ,
                'value' => $assist->$column,
                'column' => $column,
                'deleted' => $deleted
            ])
        </td>
        <td class="text-center">
            @if(!$deleted)
            <button type='button' class='btn btn-primary observation' data-toggle='modal' data-target='#modal_observation'
                data-id="{{$assist->id}}">Observaciónes
            </button>
            @endif
        </td>
    @else
        <td class="text-center">
            @include('templates.assists.select_row', [
                'id' => $assist->id,
                'index' => 0,
                'value' => $assist->$column,
                'column' => $column,
                'deleted' => $deleted,
            ])
        </td>
        {{--<td class="text-center">
            @include('templates.assists.select', [
                'id' => $assist->id,
                'index' => 0,
                'value' => $assist->$column,
                'column' => $column,
                'deleted' => $deleted,
                'observations' => $assist->observations,
                'classDay' => $classDays->firstWhere('column', $column)
            ])
        </td>--}}
        <td class="text-center">
            @if(!$deleted)
            @php
            $classDay = $classDays->firstWhere('column', $column)
            @endphp
                <a
                href="javascript:void(0)"
                class="btn btn-primary assist"
                data-toggle='modal' data-target='#modal_attendance'
                data-id="{{$assist->id}}"
                data-day="{{$classDay['day']}}"
                data-date="{{$classDay['date']}}"
                data-name="{{$classDay['name']}}"
                data-column="{{$classDay['column']}}"
                data-number="{{$classDay['number_class']}}"
                data-value="{{$assist->$column}}"
                data-observation="{{data_get($assist->observations, $classDay['date'], '')}}"
                id="{{$assist->id}}{{$classDay['day']}}"
                >Observaciónes
                </a>

            {{--<button type='button' class='btn btn-primary observation' data-toggle='modal' data-target='#modal_observation'
                data-id="{{$assist->id}}">Observaciónes
            </button>--}}
            @endif
        </td>
    @endif
</tr>