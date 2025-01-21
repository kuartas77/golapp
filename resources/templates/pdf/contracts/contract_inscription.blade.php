<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CONTRATO DE INSCRIPCIÓN</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>
    @switch($school->id)

        @case(5)
        @case(6)
            @include('templates.contracts.10pro.header')
            @include('templates.contracts.10pro.footer')
            @include('templates.contracts.10pro.contract')
            @break

        @default
            Default case...
    @endswitch

</body>

</html>