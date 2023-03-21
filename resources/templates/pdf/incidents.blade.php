<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>INCIDENCIAS {{$professor->name}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
    <style type="text/css">
        .texto {
            font-size: 9.5px;
        }

        html {
            margin-top: 0px;
            margin-bottom: 0px;
        }
    </style>
</head>
<body>

<div class="page">
    <table class="table-encabezado" style="margin:10px auto 0;" border="0">
        <tr>
            <td align="center" style="display:block;margin:auto;width: 60px;height: 60px;">
                <img src="{{ $school->logo_local }}">
            </td>
            <td></td>
            <td align="center" valign="middle">{{env('APP_NAME', 'Laravel')}}<br>INCIDENCIAS
            </td>
            <td></td>
            <td align="center" style="display:block;margin:auto;width: 60px;height: 60px;">
                <img src="{{ $school->logo_local }}">
            </td>
        </tr>
    </table>
    <br>
    <table class="table tabla-bordeada table-condensed" style="margin:0 auto 10px;">
        <tbody>
        <tr class="tr-tit">
            <td class="texto">Profesor: {{$professor->name}}</td>
            <td class="texto">Correo: {{$professor->email}}</td>
        </tr>
        </tbody>
    </table>

    <ol class="list-group">
        @foreach($incidents as $incident)
            <li class="list-group-item">

                <div style="word-wrap: break-word; width: 100px">
                    <p><strong>Titulo:</strong> {{ $incident->incidence_upper }}</p>
                </div>
                <div style="word-wrap: break-word; width: 100px">
                    <p><strong>Descripción:</strong> {!! $incident->description_upper !!}</p>
                </div>
                <div style="word-wrap: break-word; width: 100px">
                    <small><strong>Fecha:</strong> {{ $incident->created_at->format('Y-m-d h:i:s a') }}</small>
                </div>

            </li>
        @endforeach
    </ol>
</div>
</body>

</html>
