@component('mail::message')
# El siguiente listado contiene las mensualidades que al día {{now()->format('d-m-Y')}} tienen novedad de deuda.
## Cantidad: {{$payments->count()}}

@component('mail::subcopy')
### ¡Este mensaje fue enviado a todos los usuarios **Administradores**!<br>
### ¡Este es un mensaje automático, por favor no responda!
@endcomponent

@component('mail::panel')
@component('mail::table')
| Código Único                    | Deportista          | Grupo entrenamiento      |
|:--------------------------------|:--------------------|:-------------------------|
@foreach($payments as $payment)
| **{{ $payment->unique_code }}** | {{$payment->names}} | {{$payment->group_name}} |
@endforeach
@endcomponent
@endcomponent

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agreguenos a sus contactos y marque este correo como no spam.
@endcomponent

## Gracias,<br>
# {{ config('app.name') }}.
@endcomponent
