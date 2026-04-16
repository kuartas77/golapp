@component('mail::message')
# Notificación de Inscripción {{ $inscription->school->name }}

@component('mail::panel')
* Nombres: {{ $inscription->player->full_names }}
* Fecha De Inicio: {{ $inscription->start_date->format('Y-m-d') }}
* Codigo único: {{ $inscription->unique_code }}
@endcomponent

@if($sendContract)
@component('mail::panel')
* Adjunto se encuentra el contrato en formato PDF firmados.
* {{$inscription->year}}_{{$inscription->unique_code}}_CONTRATO DE INSCRIPCIÓN.pdf
@endcomponent
@endif

@if($inscription->school->tutor_platform)
@component('mail::panel')
* Podras ingresar a nuestra plataforma y verificar, actualizar la información del Deportista.
* Si el acudiente principal tiene un correo válido, recibirá un mensaje para activar su acceso y definir contraseña.
    @component('mail::button', ['url' => url('/portal/acudientes/login')])
        Plataforma
    @endcomponent
@endcomponent
@endif

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ $inscription->school->name }}.
@endcomponent
