@component('mail::message')
# Notificacion de Registro.

* Nombre: {{$user->name}}
* Correo: {{$user->email}}
* Contraseña: {{$pass}}
* Fecha de Creación: {{$user->created_at->format('d-m-Y')}}
* La Contraseña Enviada En Este Correo Puede Ser Cambiada Por Una Más Segura.

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