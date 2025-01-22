<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CONTRATO DE INSCRIPCIÓN {{$school->name}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>
    @switch($school->id)
        @case(2)
            @include('templates.contracts.felria.header')
            @include('templates.contracts.felria.footer')
            @include('templates.contracts.felria.contract')
            @break
        @case(5)
        @case(6)
        @case(7)
            @include('templates.contracts.10pro.header')
            @include('templates.contracts.10pro.footer')
            @include('templates.contracts.10pro.contract')
            @break

        @default

    @endswitch

</body>

</html>