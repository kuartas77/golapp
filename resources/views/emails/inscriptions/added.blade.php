@component('mail::message')
# Notificación de Inscripción {{ $inscription->school->name }}

@component('mail::panel')
* Nombres: {{ $inscription->player->full_names }}
* Fecha De Inicio: {{ $inscription->start_date->format('Y-m-d') }}
@endcomponent

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ $inscription->school->name }}.
@endcomponent

