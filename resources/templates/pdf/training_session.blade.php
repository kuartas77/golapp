<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SESIÓN DE ENTRENAMIENTO {{ $group->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>

<body>

    <table class="table-full title">
        <tr>
            <td class="text-left" width="20%">
                <img src="{{ $school->logo_local }}" width="70" height="70">
            </td>
            <td class="text-center school-title" width="60%">{{ $school->name }}<br>SESIÓN DE ENTRENAMIENTO</td>
            <td class="text-right" width="20%">
                <img src="{{ $school->logo_local }}" width="70" height="70">
            </td>
        </tr>
    </table>

    <table class="table-full detail detail-lines">
        <tbody>
            <tr class="tr-tit">
                <td class="center texto bold" colspan="5">Grupo: {{$group->name}}</td>
            </tr>
            <tr class="">
                <td class="texto bold">&nbsp;Formador(es): {{$group->instructors_names}}</td>
                <td class="texto bold">&nbsp;Categoria: {{implode(',',$group->category)}}</td>
                <td class="texto bold">&nbsp;Días de entrenamiento: {{$group->days}}</td>
                <td class="texto bold" colspan="2">&nbsp;Horarios: {{$group->schedules}}</td>
            </tr>

            <tr class="">
                <td class="texto bold">&nbsp;Periodo: {{$trainingSession->period}}</td>
                <td class="texto bold">&nbsp;Sesión: {{$trainingSession->session}}</td>
                <td class="texto bold">&nbsp;fecha: {{$trainingSession->date}}</td>
                <td class="texto bold">&nbsp;Hora: {{$trainingSession->hour}}</td>
                <td class="texto bold">&nbsp;Lugar: {{$trainingSession->training_ground}}</td>
            </tr>
            <tr class="">
                <td class="texto bold">&nbsp;N° Jugadores: {{$trainingSession->players}}</td>
                <td class="texto bold" colspan="4">&nbsp;Materiales Utilizados: {{$trainingSession->material}}</td>
            </tr>
            <tr class="">
                <td class="texto bold" colspan="5">&nbsp;Calentamiento: {{$trainingSession->warm_up}}</td>
            </tr>

            <tr class="">
                <td class="texto bold" colspan="5">&nbsp;Incidentes: {{$trainingSession->incidents}}</td>
            </tr>
            <tr class="">
                <td class="texto bold" colspan="5">&nbsp;Ausencias: {{$trainingSession->absences}}</td>
            </tr>
            <tr class="">
                <td class="texto bold" colspan="5">&nbsp;Vuelta a la calma: {{$trainingSession->back_to_calm}}</td>
            </tr>
            <tr class="">
                <td class="texto bold" colspan="5">&nbsp;Retro alimentación: {{$trainingSession->feedback}}</td>
            </tr>
        </tbody>
        <!-- coaches -->
    </table>

    @foreach($tasks as $task)
    <table class="table-full detail detail-lines">
        <tbody>
            <tr class="tr-tit">
                <td class="center texto bold" colspan="3">N° Ejercicio: {{$task->task_number}}</td>
            </tr>
            <tr class="">
                <td class="texto bold">&nbsp;Ejercicio: {{$task->task_name}}</td>
                <td class="texto bold">&nbsp;Objetivo General: {{$task->general_objective}}</td>
                <td class="texto bold">&nbsp;Objetivo Específico: {{$task->specific_goal}}</td>
            </tr>
            <tr class="tr-tit">
                <td class="center texto bold" colspan="3">Contenido</td>
            </tr>
            <tr class="">
                <td class="texto bold">&nbsp;Tarea: {{$task->content_one}}</td>
                <td class="texto bold">&nbsp;Tarea: {{$task->content_two}}</td>
                <td class="texto bold">&nbsp;Tarea: {{$task->content_three}}</td>
            </tr>
            <tr class="">
                <td class="texto bold">&nbsp;Tiempo Serie "TS": {{$task->ts}}</td>
                <td class="texto bold">&nbsp;Serie / Repetición "S/R": {{$task->sr}}</td>
                <td class="texto bold">&nbsp;Tiempo Total "TT": {{$task->tt}}</td>
            </tr>
            <tr class="">
                <td class="texto bold" colspan="3">&nbsp;Observaciones: {{$task->observations}}</td>
            </tr>
        </tbody>
    </table>
    @endforeach
</body>