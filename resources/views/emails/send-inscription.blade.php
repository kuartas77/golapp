@component('mail::message')
# Notificación de Inscripción

@component('mail::panel')
* Nombres: {{$inscription->full_names}}
* Fecha De Inicio: {{$inscription->start_date->format('Y-m-d')}}
@endcomponent

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ config('app.name') }}.
@endcomponent
