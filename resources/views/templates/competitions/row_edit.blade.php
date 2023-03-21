<tr>

    <td style="display: flex;">
        @if($skillControl->id)
        <input name="ids[{{$index}}]" type="hidden" value="{{$skillControl->id}}">
        @endif
        <input name="inscriptions_id[{{$index}}]" type="hidden" value="{{$inscription->id}}">
        <img class="media-object img-rounded" src="{{$inscription->player->photo_url}}" width="60" height="60">
        <p>
            {{$inscription->player->full_names}}<br>
            Teléfono: <small>{{$inscription->player->phones}}</small><br>
            Celular: <small>{{$inscription->player->mobile}}</small><br>
            Código: <strong>{{$inscription->player->unique_code}}</strong><br>
        </p>
    </td>

    <td>
        @include('templates.competitions.select', [
        'name'=> "assistance[{$index}]", 'values' => [1=>'Si',0 => 'No'], 'value' => $skillControl->assistance])
    </td>
    <td>
        @include('templates.competitions.select', [
        'name'=> "titular[{$index}]", 'values' => [1=>'Si',0 => 'No'], 'value' => $skillControl->titular])
    </td>
    <td>
        @include('templates.competitions.select', [
            'name'=> "played_approx[{$index}]", 'values' => $played, 'value' => $skillControl->played_approx])
    </td>
    <td>
        @include('templates.competitions.select', [
            'name'=> "position[{$index}]", 'values' => $positions, 'value' => $skillControl->position])
    </td>
    <td>
        @include('templates.competitions.select_g_t', [
            'name'=> "goals[{$index}]", 'values' => $scores, 'value' => $skillControl->goals])
    </td>
    <td>
        @include('templates.competitions.select_g_t', [
                'name'=> "yellow_cards[{$index}]", 'values' => [0=>0, 1=>1,2=>2], 'value' => $skillControl->yellow_cards])
    </td>
    <td>
        @include('templates.competitions.select_g_t', [
                'name'=> "red_cards[{$index}]", 'values' => [0=>0,1=>1], 'value' => $skillControl->red_cards])
    </td>
    <td>
        @include('templates.competitions.select', ['name'=> "qualification[{$index}]", 'values' => $qualifications, 'value' => $skillControl->qualification])
    </td>
    <td>
        <textarea class="form-control form-control-sm" name="observation[{{$index}}]" cols="30" rows="3">{{$skillControl->observation}}</textarea>
    </td>
</tr>
