<tr>
    <td class="text-center">
        <a href="{{$tournamentPayout->inscription->player->url_show}}" target="_blank">
            <small>{{ $tournamentPayout->unique_code }} {{ $tournamentPayout->inscription->player->full_names }}</small>
            <input type="hidden" name="id" value="{{$tournamentPayout->id}}">
        </a>
    </td>
    <td class="text-center">{{$tournamentPayout->tournament->name}}</td>
    <td class="text-center">
        @include('templates.payments.tournaments.select', ['name' => 'status', 'value' => $tournamentPayout->status, 'deleted' => $deleted])
    </td>
</tr>
