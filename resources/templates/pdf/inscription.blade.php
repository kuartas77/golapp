<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Deportista {{$player->unique_code}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}" media="all">
</head>
<body>
<table class="table-full title">
    <tr>
        <td class="text-left" width="20%">
            <img src="{{ $player->photo_file }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">{{env('APP_NAME', 'Laravel')}}<br>FICHA DEL DEPORTISTA
        </td>
        <td class="text-right" width="20%">
            <img src="{{ $player->photo_file }}" width="70" height="70">
        </td>
    </tr>
    <tr class="tr-tit">
        <td class="text-center bold" width="45%">
            <h3 class="school-title">Fecha De Registro: {{ $player->created_at->format('Y-m-d') }}</h3>
        </td>

        <td class="text-center" width="10%"></td>

        <td class="text-center bold" width="45%">
            <h3 class="school-title">Código: {{ $player->unique_code }}</h3>
        </td>
    </tr>
</table>

<table class="table-full detail detail-lines">
    <tr>
        <td><strong class="bold">Nombres:</strong> {{ $player->names }}</td>
        <td><strong class="bold">Apellidos:</strong> {{ $player->last_names }}</td>
        <td><strong class="bold">Doc. de Identidad:</strong> {{ $player->identification_document }}</td>
    </tr>
    <tr>
        @if($player->gender == 'M')
            <td><strong class="bold">Genero:</strong> Masculino</td>
        @else
            <td><strong class="bold">Genero:</strong> Femenino</td>
        @endif
        <td><strong class="bold">Fecha de Nacimiento:</strong> {{ $player->date_birth }}</td>
        <td><strong class="bold">Lugar de Nacimiento:</strong> {{ $player->place_birth }}</td>
    </tr>
    <tr>
        <td><strong class="bold">Dirección:</strong> {{ $player->address }}</td>
        <td><strong class="bold">Municipio:</strong> {{ $player->municipality }}</td>
        <td><strong class="bold">Barrio:</strong> {{ $player->neighborhood }}</td>
    </tr>
    <tr>
        <td><strong class="bold">Teléfonos:</strong> {{ $player->phones }} {{ $player->mobile }}</td>
        <td><strong class="bold">Correo Electrónico:</strong> {{ $player->email }}</td>
        <td><strong class="bold">EPS:</strong> {{ $player->eps }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong class="bold">Instituto/Colegio/Escuela:</strong> {{ $player->school }}</td>
        <td><strong class="bold">Grado:</strong> {{ $player->degree }}</td>
    </tr>
    @if($player->inscription)
    <tr>
        <td colspan="3" class="text-center"><strong class="bold">Documentos</strong></td>
    </tr>
    <tr>
        <td><strong class="bold">Fotos:</strong> {{$player->inscription->photos ? 'Si':'No'}}</td>
        <td><strong class="bold">Fotocopia Doc
                Identidad:</strong> {{$player->inscription->copy_identification_document ? 'Si':'No'}}</td>
        <td><strong class="bold">Certificado EPS:</strong> {{$player->inscription->eps_certificate ? 'Si':'No'}}</td>
    </tr>
    <tr>
        <td><strong class="bold">Certificado Médico:</strong> {{$player->inscription->medic_certificate ? 'Si':'No'}}
        </td>
        <td><strong class="bold">Certificado Estudio:</strong> {{$player->inscription->study_certificate ? 'Si':'No'}}
        </td>
        <td></td>
    </tr>
    <tr>
        <td colspan="3" class="text-center"><strong class="bold">Productos</strong></td>
    </tr>
    <tr>
        <td><strong class="bold">Peto:</strong> {{$player->inscription->overalls ? 'Si':'No'}}</td>
        <td><strong class="bold">Balón:</strong> {{$player->inscription->ball ? 'Si':'No'}}</td>
        <td><strong class="bold">Morral:</strong> {{$player->inscription->bag ? 'Si':'No'}}</td>
    </tr>
    <tr>
        <td><strong class="bold">Uniforme
                Presentación:</strong> {{$player->inscription->presentation_uniform ? 'Si':'No'}}</td>
        <td><strong class="bold">Uniforme
                Competición:</strong> {{$player->inscription->competition_uniform ? 'Si':'No'}}</td>
        <td><strong class="bold">Pagó Torneo:</strong> {{$player->inscription->tournament_pay ? 'Si':'No'}}</td>
    </tr>
    <tr>
        <td colspan="3" class="text-center"><strong class="bold">Familiares</strong></td>
    </tr>
    @endif
    @foreach($player->peoples as $people)
    <tr>
        <td colspan="3" class="text-left">
            <strong
                class="bold">{{$people->is_tutor ? '(Acudiente)' : ''}} {{\Illuminate\Support\Str::upper($people->relationship_name)}}</strong>
        </td>
    </tr>
    <tr>
        <td><strong class="bold">Nombres:</strong> {{ $people->names }}</td>
        <td><strong class="bold">Cédula:</strong> {{ $people->identification_card }}</td>
        <td><strong class="bold">Teléfonos:</strong> {{ "{$people->phone} {$people->mobile}" }}</td>
    </tr>
    <tr>
        <td><strong class="bold">Profesión:</strong> {{ $people->profession }}</td>
        <td><strong class="bold">Empresa:</strong> {{ $people->business }}</td>
        <td><strong class="bold">Cargo:</strong> {{ $people->position }}</td>
    </tr>
    @endforeach
</table>

@if($player->inscription)
<table class="table-full title">
    <tr>
        <td class="text-left" width="49%">
            @isset($player->inscription->trainingGroup)
                <strong class="bold">Grupo De Entrenamiento: {{$player->inscription->trainingGroup->name}}</strong>
            @endisset
        </td>
        <td class="text-center school-title" width="2%"></td>
        <td class="text-right" width="49%">
            @isset($player->inscription->competitionGroup)
                <strong class="bold">Grupo De Competencia: {{$player->inscription->competitionGroup->name}}</strong>
            @endisset
        </td>
    </tr>
</table>
@endif
</body>
</html>
