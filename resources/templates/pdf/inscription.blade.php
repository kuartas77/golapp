<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $school->name }} Deportista {{$player->unique_code}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}" media="all">
</head>
<body>
    <table class="table-full title">
        <tr>
            <td class="text-left" width="20%">
                <img src="{{ $school->logo_local }}" width="70" height="70">
            </td>
            <td class="text-center school-title" width="60%">{{ $school->name }}<br>FICHA DEL DEPORTISTA
            </td>
            <td class="text-right" width="20%">
                <img src="{{ $player->photo_local }}" width="70" height="70">
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
        <thead>
            <tr class="tr-tit">
                <th colspan="3" class="text-center"><strong class="bold">Deportista</strong></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class=""><strong class="bold">&nbsp;Nombres:</strong> {{ $player->names }}</td>
                <td class=""><strong class="bold">&nbsp;Apellidos:</strong> {{ $player->last_names }}</td>
                <td class=""><strong class="bold">&nbsp;Doc. de Identidad:</strong> {{ $player->identification_document }}</td>
            </tr>
            <tr>
                @if($player->gender == 'M')
                    <td class=""><strong class="bold">&nbsp;Genero:</strong> Masculino</td>
                @else
                    <td class=""><strong class="bold">&nbsp;Genero:</strong> Femenino</td>
                @endif
                <td class=""><strong class="bold">&nbsp;Fecha de Nacimiento:</strong> {{ $player->date_birth }}</td>
                <td class=""><strong class="bold">&nbsp;Lugar de Nacimiento:</strong> {{ $player->place_birth }}</td>
            </tr>
            <tr>
                <td class=""><strong class="bold">&nbsp;Dirección:</strong> {{ $player->address }}</td>
                <td class=""><strong class="bold">&nbsp;Municipio:</strong> {{ $player->municipality }}</td>
                <td class=""><strong class="bold">&nbsp;Barrio:</strong> {{ $player->neighborhood }}</td>
            </tr>
            <tr>
                <td class=""><strong class="bold">&nbsp;Teléfonos:</strong> {{ $player->phones }} {{ $player->mobile }}</td>
                <td class=""><strong class="bold">&nbsp;Correo Electrónico:</strong> {{ $player->email }}</td>
                <td class=""><strong class="bold">&nbsp;EPS:</strong> {{ $player->eps }}</td>
            </tr>
            <tr>
                <td class="" colspan="2"><strong class="bold">&nbsp;Instituto/Colegio/Escuela:</strong> {{ $player->school }}</td>
                <td class=""><strong class="bold">&nbsp;Grado:</strong> {{ $player->degree }}</td>
            </tr>
        </tbody>
    </table>

    <table class="table-full detail detail-lines">
        <thead>
            <tr class="tr-tit">
                <th colspan="3" class="text-center"><strong class="bold">Familiares</strong></th>
            </tr>
        </thead>
        @foreach($player->people as $people)
        <tr>
            <td colspan="3" class="text-left">
                <strong
                    class="bold">{{$people->is_tutor ? '(Acudiente)' : ''}} {{\Illuminate\Support\Str::upper($people->relationship_name)}}</strong>
            </td>
        </tr>
        <tr>
            <td><strong class="bold">&nbsp;Nombres:</strong> {{ $people->names }}</td>
            <td><strong class="bold">&nbsp;Cédula:</strong> {{ $people->identification_card }}</td>
            <td><strong class="bold">&nbsp;Teléfonos:</strong> {{ "{$people->phone} {$people->mobile}" }}</td>
        </tr>
        <tr>
            <td><strong class="bold">&nbsp;Profesión:</strong> {{ $people->profession }}</td>
            <td><strong class="bold">&nbsp;Empresa:</strong> {{ $people->business }}</td>
            <td><strong class="bold">&nbsp;Cargo:</strong> {{ $people->position }}</td>
        </tr>
        @endforeach
    </table>

    @if($player->inscription)
    <table class="table-full detail detail-lines">
        <tr>
            <td colspan="3" class="text-center"><strong class="bold">Documentos</strong></td>
        </tr>
        <tr>
            <td><strong class="bold">&nbsp;Fotos:</strong> {{$player->inscription->photos ? 'Si':'No'}}</td>
            <td><strong class="bold">&nbsp;Fotocopia Doc Identidad:</strong> {{$player->inscription->copy_identification_document ? 'Si':'No'}}</td>
            <td><strong class="bold">&nbsp;Certificado EPS:</strong> {{$player->inscription->eps_certificate ? 'Si':'No'}}</td>
        </tr>
        <tr>
            <td><strong class="bold">&nbsp;Certificado Médico:</strong> {{$player->inscription->medic_certificate ? 'Si':'No'}}
            </td>
            <td><strong class="bold">&nbsp;Certificado Estudio:</strong> {{$player->inscription->study_certificate ? 'Si':'No'}}
            </td>
            <td></td>
        </tr>
        <tr>
            <td colspan="3" class="text-center"><strong class="bold">Productos</strong></td>
        </tr>
        <tr>
            <td><strong class="bold">&nbsp;Pagó Inscripción Torneo 1:</strong> {{$player->inscription->tournament_pay ? 'Si':'No'}}</td>
            <td><strong class="bold">&nbsp;Pagó Inscripción Torneo 2:</strong> {{$player->inscription->bag ? 'Si':'No'}}</td>
            <td><strong class="bold">&nbsp;Pagó Inscripción Torneo 3:</strong> {{$player->inscription->ball ? 'Si':'No'}}</td>
        </tr>
        <tr>
            <td><strong class="bold">&nbsp;Peto:</strong> {{$player->inscription->overalls ? 'Si':'No'}}</td>
            <td><strong class="bold">&nbsp;Uniforme Presentación:</strong> {{$player->inscription->presentation_uniform ? 'Si':'No'}}</td>
            <td><strong class="bold">&nbsp;Uniforme Competición:</strong> {{$player->inscription->competition_uniform ? 'Si':'No'}}</td>
        </tr>
    </table>

    <table class="table-full title">
        <tr>
            <td class="text-left" width="49%">
                @isset($player->inscription->trainingGroup)
                    <strong class="bold">&nbsp;Grupo De Entrenamiento: {{$player->inscription->trainingGroup->name}}</strong>
                @endisset
            </td>
            <td class="text-center school-title" width="2%"></td>
            <td class="text-right" width="49%">
                @isset($player->inscription->competitionGroup)
                    <strong class="bold">&nbsp;Grupo De Competencia: {{$player->inscription->competitionGroup->name}}</strong>
                @endisset
            </td>
        </tr>
    </table>
    @endif
</body>
</html>
