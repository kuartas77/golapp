<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado de paz y salvo</title>
    <style>
        body { color: #233044; font-family: sans-serif; font-size: 12px; line-height: 1.65; }
        .header { border-bottom: 3px solid #198754; padding-bottom: 12px; }
        .logo { width: 78px; height: 78px; object-fit: contain; }
        .school-name { color: #17324d; font-size: 20px; font-weight: bold; }
        .school-data { color: #637083; font-size: 10px; }
        .title-box { background: #e9f7ef; border: 1px solid #9bd5b2; border-radius: 8px; margin: 28px 0 24px; padding: 16px; text-align: center; }
        .title { color: #146c43; font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .subtitle { color: #527060; font-size: 11px; }
        .player-card { background: #f6f8fb; border-left: 4px solid #17324d; margin-bottom: 24px; padding: 14px 18px; }
        .label { color: #637083; font-size: 10px; text-transform: uppercase; }
        .value { font-size: 13px; font-weight: bold; }
        .statement { font-size: 13px; text-align: justify; }
        .note { color: #637083; font-size: 10px; margin-top: 22px; text-align: center; }
        .signature { margin: 72px auto 0; text-align: center; width: 52%; }
        .signature-line { border-top: 1px solid #233044; padding-top: 7px; }
        .issued { color: #637083; font-size: 10px; margin-top: 35px; text-align: right; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: middle; }
    </style>
</head>
<body>
<table class="header">
    <tr>
        <td width="18%">
            <img class="logo" src="{{ $school->logo_local }}" alt="Logo">
        </td>
        <td width="82%">
            <div class="school-name">{{ $school->name }}</div>
            <div class="school-data">
                {{ $school->address ?: 'Dirección no registrada' }} · Tel. {{ $school->phone ?: 'No registrado' }}<br>
                {{ $school->email ?: ($school->email_info ?: 'Correo no registrado') }}
            </div>
        </td>
    </tr>
</table>

<div class="title-box">
    <div class="title">CERTIFICADO DE PAZ Y SALVO</div>
    <div class="subtitle">Estado financiero verificado a la fecha de expedición</div>
</div>

<div class="player-card">
    <table>
        <tr>
            <td width="50%"><span class="label">Deportista</span><br><span class="value">{{ $player->full_names }}</span></td>
            <td width="25%"><span class="label">Documento</span><br><span class="value">{{ $player->identification_document }}</span></td>
            <td width="25%"><span class="label">Código único</span><br><span class="value">{{ $player->unique_code }}</span></td>
        </tr>
    </table>
</div>

<p class="statement">
    <strong>{{ $school->name }}</strong> certifica que, después de revisar el historial financiero completo del deportista
    <strong>{{ $player->full_names }}</strong>, no se encuentran obligaciones vencidas ni saldos pendientes por conceptos
    de inscripción, mensualidades, facturas o cobros personalizados registrados por la institución.
</p>

<p class="statement">
    El presente certificado se expide a solicitud del interesado para los trámites de traslado o vinculación ante otra
    escuela, club o entidad deportiva.
</p>

<div class="signature">
    <div class="signature-line">
        <strong>{{ $school->agent ?: 'Responsable autorizado' }}</strong><br>
        Responsable autorizado<br>
        {{ $school->name }}
    </div>
</div>

<div class="note">
    Este documento certifica exclusivamente el estado financiero del deportista y no acredita condiciones deportivas,
    disciplinarias ni contractuales distintas de las obligaciones aquí descritas.
</div>

<div class="issued">Expedido el {{ $issuedAt->format('d/m/Y') }} a las {{ $issuedAt->format('h:i A') }}</div>
</body>
</html>
