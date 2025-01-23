<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
    <style>
        p {
            text-align: justify;
            text-justify: inter-word;
        }
    </style>
</head>
<body>

    @switch($school->id)
        @case(2)
            @include('templates.contracts.felria.header')
            @include('templates.contracts.felria.footer')
            @include('templates.contracts.felria.affiliate')
            @break
        @default

    @endswitch

</body>

</html>