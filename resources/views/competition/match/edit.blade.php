@extends('layouts.app')
@section('css')
    @include('competition.match.styles')
@endsection
@section('content')
    <x-bread-crumb title="Control De Competencia" :option="0"/>
    {{html()->modelForm($information->match, 'put', $information->match->url_update)->attributes(['id'=>'form_matches','class'=>'form-horizontal'])->open()}}
        <div class="row match-layout">
            <div class="col-lg-4 col-xl-3 mb-3">
                <div class="card m-b-0 match-sidebar-card match-sticky-card">
                    <div class="card-body match-card-body">
                        @include('competition.match.fields')

                        <div class="match-save-bar" id="button_save">
                            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-xl-9">
                <div class="card m-b-0 match-players-card">
                    <div class="card-body match-card-body">
                        <div class="match-table-toolbar">
                            <div>
                                <h4 class="match-table-title">Jugadores</h4>
                                <small class="text-muted">Actualiza minutos, desempeño y observaciones del partido.</small>
                            </div>
                            <span class="match-table-count">Total <strong id="members_count">{{ $information->count }}</strong></span>
                        </div>

                        @include('competition.match.table_members')
                    </div>
                </div>
            </div>
        </div>
    {{ html()->closeModelForm() }}
@endsection
@section('modals')
    @include('modals.add_member_match')
@endsection
@section('scripts')
    <script>
        let count = {{$information->count}};
        let member_add = null;
        const urlList = "{{route('autocomplete.list_code_unique_inscription')}}";
        const urlSearch = "{{route('autocomplete.search_unique_code')}}";
        const urlAutoComplete = "{{route('autocomplete.fields')}}";
        const positions = @json($positions);
    </script>
    <script src="{{asset('js/matches_functions.js')}}"></script>
    <script src="{{asset('js/matches_form.js')}}"></script>
    <script>
        $(document).ready(() => {
            $("#accept_add").on('click', () => {
                let member = '<tr>' +
                    buildMemberIdentityCell({
                        player: member_add.player,
                        count,
                        inscriptionId: member_add.id,
                        includeIdField: true
                    }) +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="assistance[' + count + ']">' + selectOptions() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="titular[' + count + ']">' + selectOptions() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="played_approx[' + count + ']">' + selectMinutes() + '</select></td>' +
                    '<td class="match-position-cell"><select class="form-control form-control-sm select" name="position[' + count + ']" required>' + selectPositions() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="goals[' + count + ']">' + selectGoals() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="goal_assists[' + count + ']">' + selectGoals() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="goal_saves[' + count + ']">' + selectGoals() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="yellow_cards[' + count + ']">' + selectYellowCards() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="red_cards[' + count + ']">' + selectRedCards() + '</select></td>' +
                    '<td class="match-metric-cell"><select class="form-control form-control-sm select" name="qualification[' + count + ']" required>' + selectScore() + '</select></td>' +
                    '<td class="match-observation-cell"><textarea class="form-control form-control-sm match-observation-field" name="observation[' + count + ']" cols="30" rows="2"></textarea></td>' +
                    '</tr>';

                $('#body_members').prepend(member);
                count++;
                updateMembersCount();
                cancelAddMember();
                $("#modal_search_member").modal('hide');
            });

            $("#cancel_add").on('click', () => {
                cancelAddMember();
            });

            updateMembersCount();
        });
    </script>
@endsection
