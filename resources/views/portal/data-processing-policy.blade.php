<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Política de tratamiento de datos - {{ $school->name }}</title>
    <style>
        body { margin: 0; background: #f5f7fb; color: #253044; font-family: Arial, sans-serif; line-height: 1.6; }
        main { max-width: 860px; margin: 32px auto; padding: 32px; background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(31, 45, 61, .08); }
        h1, h2 { color: #183153; }
        h1 { margin-top: 0; font-size: 1.8rem; }
        h2 { margin-top: 28px; font-size: 1.2rem; }
        .meta { color: #667085; }
        .notice { padding: 16px; border-left: 4px solid #2f80ed; background: #eef6ff; }
        li { margin-bottom: 8px; }
        a { color: #1769aa; }
    </style>
</head>
<body>
<main>
    <h1>Política de tratamiento de datos personales</h1>
    <p class="meta">Responsable: <strong>{{ $school->name }}</strong> · Versión {{ $policy['version'] }}</p>

    <p>
        {{ $school->name }} es responsable del tratamiento de los datos personales recolectados durante
        la inscripción. La información será tratada de acuerdo con la legislación colombiana aplicable
        y únicamente para las finalidades informadas en esta política.
    </p>

    <h2>Finalidades del tratamiento</h2>
    <ul>
        @foreach ($policy['purposes'] as $purpose)
            <li>{{ $purpose }}</li>
        @endforeach
    </ul>

    <h2>Datos de menores y datos sensibles</h2>
    <p>
        El acudiente autoriza el tratamiento de los datos del menor que representa y declara que entrega
        información veraz. Los datos de salud, antecedentes médicos, certificados y demás información
        sensible serán utilizados únicamente para la seguridad, atención y gestión deportiva del menor,
        con acceso restringido al personal autorizado.
    </p>

    <h2>Evidencia de aceptación y firma electrónica</h2>
    <p>
        Para conservar trazabilidad del proceso, el sistema puede registrar la fecha y hora de aceptación,
        la dirección IP, información del navegador o dispositivo, la versión de esta política y los hashes
        criptográficos de los documentos firmados. Estos datos se utilizan para verificar la integridad y
        las circunstancias de la inscripción.
    </p>

    <h2>Derechos del titular</h2>
    <ul>
        @foreach ($policy['rights'] as $right)
            <li>{{ $right }}</li>
        @endforeach
    </ul>

    <h2>Canales de atención</h2>
    <p>
        Las consultas y solicitudes relacionadas con datos personales pueden dirigirse a
        <strong>{{ $policy['controller']['email'] ?: 'la escuela por sus canales oficiales' }}</strong>.
        @if ($policy['controller']['phone'])
            También puede comunicarse al teléfono {{ $policy['controller']['phone'] }}.
        @endif
        @if ($policy['controller']['address'])
            Dirección: {{ $policy['controller']['address'] }}.
        @endif
    </p>

    <p class="notice">
        Al marcar la autorización en el formulario de inscripción, el acudiente confirma que leyó esta
        política y autoriza el tratamiento descrito para sus datos y los del menor que representa.
    </p>
</main>
</body>
</html>
