@extends('layouts.portal.public')
@section('title', 'Mis jugadores')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-themecolor">Mis jugadores vigentes</h2>
                    <p class="card-text text-muted mb-0">
                        {{ $guardian->names }}, aquí puedes consultar la información de los deportistas con inscripción activa en el año actual.
                    </p>
                </div>
            </div>
        </div>

        @php
            $playersBySchool = $players->groupBy(fn ($player) => optional($player->schoolData)->id ?? 'sin-escuela');
        @endphp

        @forelse($playersBySchool as $schoolPlayers)
            @php
                $currentSchool = $schoolPlayers->first()?->schoolData;
            @endphp
            <div class="col-12">
                <h4 class="text-themecolor m-t-20 m-b-10">{{ $currentSchool?->name ?? 'Escuela' }}</h4>
            </div>

            @foreach($schoolPlayers as $player)
                @php
                    $currentInscription = $player->inscriptions->firstWhere('year', now()->year);
                @endphp
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center m-b-15">
                                <img src="{{ $player->photo_url_public }}" class="img-fluid rounded img-thumbnail m-r-10" width="72" height="72" alt="{{ $player->full_names }}">
                                <div>
                                    <h5 class="text-themecolor m-b-5">{{ $player->full_names }}</h5>
                                    <p class="m-b-5">@lang('messages.unique_code', ['unique_code'=> $player->unique_code])</p>
                                    <small class="text-muted">{{ optional($currentInscription?->trainingGroup)->name ?? 'Sin grupo asignado' }}</small>
                                </div>
                            </div>

                            <a class="btn btn-info btn-block" href="{{ route('portal.guardians.players.show', [$player]) }}">
                                Ver detalle
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    No hay jugadores vigentes disponibles para este acudiente.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
@section('scripts')
@endsection
