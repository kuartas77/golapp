@component('mail::message')
<div style="text-align: center; margin-bottom: 24px;">
    <img
        src="{{ asset('img/logo-light.svg') }}"
        alt="Logo GOLAPP"
        style="max-width: 220px; height: auto;"
    >
</div>

# Restablece tu contraseña

Hola {{ $user->name }},

Recibimos una solicitud para restablecer tu contraseña de acceso a GOLAPP.

@component('mail::button', ['url' => $resetUrl])
Restablecer contraseña
@endcomponent

Si no reconoces esta solicitud, puedes ignorar este mensaje.

@component('mail::subcopy')
Para que futuros correos lleguen a tu bandeja de entrada, por favor agréganos a tus contactos y marca este correo como no spam.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ config('app.name') }}.
@endcomponent
