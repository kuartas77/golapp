@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Control De Competencia" :option="0"/>
    <x-row-card col-inside="12" >
        {{html()->modelForm($information->match, 'put', $information->match->url_update)->attributes(['id'=>'form_matches','class'=>'form-horizontal'])->open()}}
            <div class="form-body">
                @include('competition.match.fields')

                @include('competition.match.table_members')
            </div>
            <div class="form-actions m-t-0 text-center" id="button_save">
                <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">
                    Guardar Cambios
                </button>
            </div>
        {{ html()->closeModelForm() }}
    </x-row-card >
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
                    '<td style="display: flex;">' +
                    '<input name="ids[' + count + ']" type="hidden" value="">' +
                    '<input name="inscriptions_id[' + count + ']" type="hidden" value="' + member_add.id + '" class="inscriptions">' +
                    '<img class="media-object img-rounded" src="' + member_add.player.photo_url + '" width="60" height="60">' +
                    '<p>' +
                    member_add.player.full_names + '<br>' +
                    'Teléfono: <small>' + member_add.player.phones + '</small><br>' +
                    'Celular: <small>' + member_add.player.mobile + '</small><br>' +
                    'Código: <strong>' + member_add.player.unique_code + '</strong><br>' +
                    '</p>' +
                    '</td>' +
                    '<td><select class="form-control form-control-sm select" name="assistance[' + count + ']">' + selectOptions() + '</select></td>' +
                    '<td><select class="form-control form-control-sm select" name="titular[' + count + ']">' + selectOptions() + '</select></td>' +
                    '<td><select class="form-control form-control-sm select" name="played_approx[' + count + ']">' + selectMinutes() + '</select></td>' +
                    '<td><select class="form-control form-control-sm select" name="position[' + count + ']" required>' + selectPositions() + '</select></td>' +
                    '<td><select class="form-control form-control-sm select" name="goals[' + count + ']">' + selectGoals() + '</select></td>' +
                    '<td><select class="form-control form-control-sm select" name="yellow_cards[' + count + ']">' + selectYellowCards() + '</select></td>' +
                    '<td><select class="form-control form-control-sm select" name="red_cards[' + count + ']">' + selectRedCards() + '</select></td>' +
                    '<td><select class="form-control form-control-sm select" name="qualification[' + count + ']" required>' + selectScore() + '</select></td>' +
                    '<td><textarea class="form-control form-control-sm" name="observation[' + count + ']" cols="30" rows="3"></textarea></td> ' +
                    '</tr>';

                $('#body_members').prepend(member);
                count++;
                cancelAddMember();
                $("#modal_search_member").modal('hide');
            });

            $("#cancel_add").on('click', () => {
                cancelAddMember();
            });
        });
    </script>
@endsection
