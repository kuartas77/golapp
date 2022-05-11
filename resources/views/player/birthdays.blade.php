@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Cumpleaños" :option="0"/>
    <x-row-card col-inside="12" >
        <h4 class="text-themecolor card-subtitle">Cumpleños Del Día</h4>
        <table class="display compact" id="table_players" width="100%">
            <thead>
            <tr>
                <th>Foto</th>
                <th>Nombres</th>
                <th>Código</th>
                <th>F.Nacimiento</th>
            </tr>
            </thead>
            <tbody>
                @forelse ($birthdays as $birthday)
                <tr>
                    <td><img class='media-object img-rounded' src="{{$birthday->photo_url}}" width="60" height="60" alt="{{$birthday->full_names}}"></td>
                    <td>{{$birthday->full_names}}</td>
                    <td>{{$birthday->unique_code}}</td>
                    <td>{{$birthday->date_birth}}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">No Hay Cumpleaños Hoy</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-row-card>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('#table_players').DataTable({"searching": false});
    });
</script>
@endsection