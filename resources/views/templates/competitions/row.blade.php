<tr>
    <td class="match-player-cell">
        <input name="inscriptions_id[{{$index}}]" type="hidden" value="{{$inscription->id}}">
        <div class="match-player-meta">
            <img class="match-player-avatar" src="{{$inscription->player->photo_url}}" alt="{{$inscription->player->full_names}}">
            <div>
                <span class="match-player-name">{{$inscription->player->full_names}}</span>
                <span class="match-player-code">{{$inscription->player->unique_code}}</span>
                @if($inscription->player->phones || $inscription->player->mobile)
                    <small class="text-muted match-player-contact">
                        @if($inscription->player->phones)
                            Tel: {{$inscription->player->phones}}
                        @endif
                        @if($inscription->player->phones && $inscription->player->mobile)
                            |
                        @endif
                        @if($inscription->player->mobile)
                            Cel: {{$inscription->player->mobile}}
                        @endif
                    </small>
                @endif
            </div>
        </div>
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select', [
        'name'=> "assistance[{$index}]", 'values' => [1=>'Sí',0 => 'No'], 'value' => 0])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select', [
        'name'=> "titular[{$index}]", 'values' => [1=>'Sí',0 => 'No'], 'value' => 0])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select', [
            'name'=> "played_approx[{$index}]", 'values' => $played, 'value' => 0])
    </td>
    <td class="match-position-cell">
        @include('templates.competitions.select', [
            'name'=> "position[{$index}]", 'values' => $positions, 'value' => null])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select_g_t', [
            'name'=> "goals[{$index}]", 'values' => $scores, 'value' => 0])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select_g_t', [
            'name'=> "goal_assists[{$index}]", 'values' => $scores, 'value' => 0])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select_g_t', [
            'name'=> "goal_saves[{$index}]", 'values' => $scores, 'value' => 0])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select_g_t', [
                'name'=> "yellow_cards[{$index}]", 'values' => ['0'=>'0', '1'=>'1','2'=>'2'], 'value' => 0])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select_g_t', [
                'name'=> "red_cards[{$index}]", 'values' => ['0'=>'0','1'=>'1'], 'value' => 0])
    </td>
    <td class="match-metric-cell">
        @include('templates.competitions.select', ['name'=> "qualification[{$index}]", 'values' => $qualifications, 'value' => null])
    </td>
    <!-- <td class="match-observation-cell">
        <textarea class="form-control form-control-sm match-observation-field" name="observation[{{$index}}]" cols="30" rows="2"></textarea>
    </td> -->
</tr>
