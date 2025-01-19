@component('mail::message')
# Notificación De Nueva Inscripción {{ $school->name }}

@component('mail::panel')
* Revisa la documentación adjunta en este correo se envía un archivo compromido.
* Descomprimelo y guarda los archivos en el Drive de Gmail de la escuela
* Nombres: {{$inscription->player->full_names}}
* Código Unico: {{$inscription->unique_code}}
@endcomponent

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ $school->name }}.
@endcomponent