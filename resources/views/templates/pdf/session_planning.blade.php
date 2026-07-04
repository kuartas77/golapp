<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><title>PLANIFICACIÓN DE SESIÓN</title>
<link rel="stylesheet" href="{{ public_path('css/dompdf.css') }}"><link rel="stylesheet" href="{{ public_path('css/dompdf-overrides.css') }}"></head>
<body>
<table class="table-full title"><tr><td width="20%"><img src="{{ $school->logo_local }}" width="70" height="70"></td><td class="text-center school-title" width="60%">{{ $school->name }}<br>PLANIFICACIÓN DE SESIÓN</td><td width="20%"></td></tr></table>
<table class="table-full detail detail-lines"><tbody>
<tr class="tr-tit"><td class="center texto bold" colspan="4">Grupo: {{ $group->name }}</td></tr>
<tr><td class="texto bold">Periodo: {{ $trainingSession->period }}</td><td class="texto bold">Sesión: {{ $trainingSession->session }}</td><td class="texto bold">Fecha: {{ $trainingSession->date }}</td><td class="texto bold">Lugar: {{ $trainingSession->training_ground }}</td></tr>
<tr><td class="texto bold" colspan="2">Materiales: {{ $trainingSession->material }}</td><td class="texto bold" colspan="2">Calentamiento: {{ $trainingSession->warm_up }}</td></tr>
</tbody></table>
@foreach($phases as $phase)
<table class="table-full detail detail-lines"><tbody>
<tr class="tr-tit"><td class="center texto bold" colspan="2">Fase {{ $phase->position }}: {{ $phase->name }}</td></tr>
<tr><td width="45%" rowspan="3" class="center">@include('templates.pdf.methodology.partials.field-diagram', ['items' => $phase->diagram])</td><td class="texto bold">Tiempo: {{ $phase->time }}</td></tr>
<tr><td class="texto bold">Dosificación: {{ $phase->dosage }}</td></tr><tr><td class="texto bold">Descripción: {{ $phase->description }}</td></tr>
</tbody></table>
@endforeach
<table class="table-full detail detail-lines"><tbody><tr><td class="texto bold">Deportistas que faltaron: {{ $resolvedAbsences }}</td></tr><tr><td class="texto bold">Vuelta a la calma: {{ $trainingSession->back_to_calm }}</td></tr><tr><td class="texto bold">Incidencias: {{ $trainingSession->incidents }}</td></tr><tr><td class="texto bold">Retroalimentación: {{ $trainingSession->feedback }}</td></tr></tbody></table>
</body></html>
