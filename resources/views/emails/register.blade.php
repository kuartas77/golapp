@component('mail::message')
# Notificacion de Registro.

* Nombre: {{$user->name}}
* Correo: {{$user->email}}
* Contraseña: {{$pass}}
* Fecha de creación: {{$user->created_at->format('d-m-Y')}}
* La contraseña enviada en este correo debe ser cambiada por una personal y más segura, desde la opción "Recuperar contraseña".

@component('mail::panel')
    @component('mail::button', ['url' => config('app.url')])
        Ir Sitio
    @endcomponent
@endcomponent

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agreguenos a sus contactos y marque este correo como no spam.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ config('app.name') }}.
@endcomponent