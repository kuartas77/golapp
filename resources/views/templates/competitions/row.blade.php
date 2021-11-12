<tr>
    <td style="display: flex;">
        <input name="inscriptions_id[{{$index}}]" type="hidden" value="{{$inscription->id}}">
        <img class="media-object img-rounded" src="{{$inscription->player->photo}}" width="60" height="60">
        <p>
            {{$inscription->player->full_names}}<br>
            Teléfono: <small>{{$inscription->player->phones}}</small><br>
            Celular: <small>{{$inscription->player->mobile}}</small><br>
            Código: <strong>{{$inscription->player->unique_code}}</strong><br>
        </p>
    </td>
    <td>
        @include('templates.competitions.select', [
        'name'=> "assistance[{$index}]", 'values' => [1=>'Sí',0 => 'No'], 'value' => 0])
    </td>
    <td>
        @include('templates.competitions.select', [
        'name'=> "titular[{$index}]", 'values' => [1=>'Sí',0 => 'No'], 'value' => 0])
    </td>
    <td>
        @include('templates.competitions.select', [
            'name'=> "played_approx[{$index}]", 'values' => $played, 'value' => 0])
    </td>
    <td>
        @include('templates.competitions.select', [
            'name'=> "position[{$index}]", 'values' => $positions, 'value' => null])
    </td>
    <td>
        @include('templates.competitions.select_g_t', [
            'name'=> "goals[{$index}]", 'values' => $scores, 'value' => 0])
    </td>
    <td>
        @include('templates.competitions.select_g_t', [
                'name'=> "yellow_cards[{$index}]", 'values' => ['0'=>'0', '1'=>'1','2'=>'2'], 'value' => 0])
    </td>
    <td>
        @include('templates.competitions.select_g_t', [
                'name'=> "red_cards[{$index}]", 'values' => ['0'=>'0','1'=>'1'], 'value' => 0])
    </td>
    <td>
        @include('templates.competitions.select', ['name'=> "qualification[{$index}]", 'values' => $qualifications, 'value' => null])
    </td>
    <td>
        <textarea class="form-control form-control-sm" name="observation[{{$index}}]" cols="30" rows="3"></textarea>
    </td>
</tr>
