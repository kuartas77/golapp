@component('mail::message')
# Notificación de Inscripción {{ $inscription->school->name }}

@component('mail::panel')
* Nombres: {{ $inscription->player->full_names }}
* Fecha De Inicio: {{ $inscription->start_date->format('Y-m-d') }}
@if($sendContract)
* Adjunto se encuentran los contratos en PDF firmados.
* {{$inscription->year}}_{{$inscription->unique_code}}_CONTRATO DE INSCRIPCIÓN
* {{$inscription->year}}_{{$inscription->unique_code}}_CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA
@endif
@endcomponent

@component('mail::panel')
* Podras ingresar a nuestra plataforma y verificar, actualizar la información del Deportista.
* Puedes Ingresar con el documento de identidad del deportista y su código único.
    @component('mail::button', ['url' => route("portal.login.form")])
        Plataforma
    @endcomponent
@endcomponent

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ $inscription->school->name }}.
@endcomponent
