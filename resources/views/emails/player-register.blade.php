@component('mail::message')
# Notificación de Deportista Registrado

* Nombre: {{$player->full_names}}
* Correo: {{$player->email}}
* Fecha de Creación: {{$player->created_at->format('d-m-Y')}}
* El Deportista Ha Sido Registrado Correctamente.
* Este Correo Ha Sido Registrado Para Recibir Las Notificaciones Por Parte De <strong>{{$player->schoolData->name}}</strong>.

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{$player->schoolData->name}}.
@endcomponent