<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CONTRATO DE INSCRIPCIÓN {{$school->name}}</title>
    <link rel="stylesheet" href="{{ public_path('css/dompdf.css') }}">

</head>

<body>

    <htmlpageheader name="page-header">
        {!!$header!!}
    </htmlpageheader>
        {!!$body!!}
    <htmlpagefooter name="page-footer">
        {!!$footer!!}
    </htmlpagefooter>

</body>

</html>
