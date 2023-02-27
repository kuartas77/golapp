@component('mail::message')
# Notificación de deportista registrado

* Nombre: {{$player->full_names}}
* Correo: {{$player->email}}
* Fecha de creación: {{$player->created_at->format('d-m-Y')}}
* El deportista ha sido registrado correctamente.
* Este correo ha sido registrado para recibir las notificaciones por parte de <strong>{{$player->schoolData->name}}</strong>.

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{$player->schoolData->name}}.
@endcomponent