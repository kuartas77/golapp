<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Deportista {{$player->unique_code}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}" media="all">
</head>
<body>
<table class="table-encabezado" style="margin:10px auto 0;" border="0">
    <tr>
        <td align="center" style="display:block;margin:auto;width: 90px;height: 90px;">
            <img src="{{ asset('ms-icon-310x310.png') }}">
        </td>
        <td align="center" valign="middle">{{env('APP_NAME', 'Laravel')}}<br>FICHA DEL DEPORTISTA
        </td>
        <td align="center" style="display:block;margin:auto;width: 90px;height: 90px;">
            <img src="{{ $player->photo_file }}">
        </td>
    </tr>
</table>
<br>

<table class="table table-condensed tabla-bordeada">
    <tr class="tr-tit">
        <td colspan="3" class="center"><strong>Fecha De Registro:</strong> {{ $player->created_at->format('Y-m-d') }}</td>
    </tr>
    <tr class="tr-tit">
        <td colspan="3" class="center"><h3><strong>INFORMACIÓN PERSONAL DEL DEPORTISTA</strong> <strong>Código:</strong> {{ $player->unique_code }}</h3></td>
    </tr>
    <tr>
        <td><strong>Nombres:</strong> {{ $player->names }}</td>
        <td><strong>Apellidos:</strong> {{ $player->last_names }}</td>
        <td><strong>Doc. de Identidad:</strong> {{ $player->identification_document }}</td>
    </tr>
    <tr>
        @if($player->gender == 'M')
            <td><strong>Genero:</strong> Masculino</td>
        @else
            <td><strong>Genero:</strong> Femenino</td>
        @endif
        <td><strong>Fecha de Nacimiento:</strong> {{ $player->date_birth }}</td>
        <td><strong>Lugar de Nacimiento:</strong> {{ $player->place_birth }}</td>
    </tr>
    <tr>
        <td><strong>Dirección:</strong> {{ $player->address }}</td>
        <td><strong>Municipio:</strong> {{ $player->municipality }}</td>
        <td><strong>Barrio:</strong> {{ $player->neighborhood }}</td>
    </tr>
    <tr>
        <td><strong>Teléfonos:</strong> {{ $player->phones }} {{ $player->mobile }}</td>
        <td><strong>Correo Electrónico:</strong> {{ $player->email }}</td>
        <td><strong>EPS:</strong> {{ $player->eps }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Instituto/Colegio/Escuela:</strong> {{ $player->school }}</td>
        <td><strong>Grado:</strong> {{ $player->degree }}</td>
    </tr>
<!--    <tr>
        <td colspan="3" class="center"><strong>DOCUMENTOS</strong></td>
    </tr>
    <tr>
        <td><strong>Fotos:</strong> {{$player->photos ? 'Si':'No'}}</td>
        <td><strong>Fotocopia Doc Identidad:</strong> {{$player->copy_identification_document ? 'Si':'No'}}</td>
        <td><strong>Certificado EPS:</strong> {{$player->eps_certificate ? 'Si':'No'}}</td>
    </tr>
    <tr>
        <td><strong>Certificado Médico:</strong> {{$player->medic_certificate ? 'Si':'No'}}</td>
        <td><strong>Certificado Estudio:</strong> {{$player->study_certificate ? 'Si':'No'}}</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="3" class="center"><strong>PRODUCTOS</strong></td>
    </tr>
    <tr>
        <td><strong>Peto:</strong> {{$player->overalls ? 'Si':'No'}}</td>
        <td><strong>Balón:</strong> {{$player->ball ? 'Si':'No'}}</td>
        <td><strong>Morral:</strong> {{$player->bag ? 'Si':'No'}}</td>
    </tr>
    <tr>
        <td><strong>Uniforme Presentación:</strong> {{$player->presentation_uniform ? 'Si':'No'}}</td>
        <td><strong>Uniforme Competición:</strong> {{$player->competition_uniform ? 'Si':'No'}}</td>
        <td><strong>Pagó Torneo:</strong> {{$player->tournament_pay ? 'Si':'No'}}</td>
    </tr>-->

</table>
@foreach($player->peoples as $people)
    <table class="table table-condensed tabla-bordeada" style="margin:10px auto 0;">
        <tr class="tr-tit">
            <td colspan="3" class="center"><strong>{{$people->is_tutor ? '(Acudiente)' : ''}} {{\Illuminate\Support\Str::upper($people->relationship_name)}}</strong></td>
        </tr>
        <tr>
            <td>Nombres: {{ $people->names }}</td>
            <td>Cédula: {{ $people->identification_card }}</td>
            <td>Teléfonos: {{ "{$people->phone} {$people->mobile}" }}</td>
        </tr>
        <tr>
            <td>Profesión: {{ $people->profession }}</td>
            <td>Empresa: {{ $people->business }}</td>
            <td>Cargo: {{ $people->position }}</td>
        </tr>
    </table>
@endforeach

</body>
</html>
