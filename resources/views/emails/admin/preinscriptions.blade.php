@component('mail::message')
# El siguiente listado contiene las preinscripciones y/o inscripciones en el Grupo (Provicional).
## Cantidad: {{$inscriptions->count()}}

@component('mail::subcopy')
### ¡Este mensaje fue enviado a todos los usuarios **Administradores**!<br>
### ¡Este es un mensaje automático, por favor no responda!
Cabe resaltar que las inscripciones marcadas como "Preinscripciones" ó que estén en el "Grupo (Provicional)" no tendrán **Asistencias, Pagos** hasta que se cambie el estado.
@endcomponent

@component('mail::panel')
@component('mail::table')
| Código Único                        | Deportista              |
|:------------------------------------|:------------------------|
@foreach($inscriptions as $inscription)
| **{{ $inscription->unique_code }}** | {{$inscription->names}} |
@endforeach
@endcomponent
@endcomponent

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agreguenos a sus contactos y marque este correo como no spam.
@endcomponent

## Gracias,<br>
# {{ config('app.name') }}.
@endcomponent
