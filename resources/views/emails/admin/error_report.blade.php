@component('mail::message')
# Error Reportado

* Mensaje: {{ $message }}
* Código: {{ $context['code'] }}
* Linea: {{ $context['line'] }}
* Archivo: {{ $context['file'] }}
* Error: {{ $context['error'] }}

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>.
@endcomponent