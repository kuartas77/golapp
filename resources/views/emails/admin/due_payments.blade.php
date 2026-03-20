@component('mail::message')
# Mensualidades en deuda al día {{ $reportDate }}

## Cantidad: {{ $payments->count() }}

@component('mail::subcopy')
Este mensaje fue enviado a los usuarios con rol **Administrador**.
Este es un mensaje automático, por favor no responder.
@endcomponent

@component('mail::panel')
@component('mail::table')
| Código Único | Deportista | Grupo entrenamiento |
|:-------------|:-----------|:--------------------|
@foreach($payments->take(100) as $payment)
| **{{ $payment->unique_code }}** | {{ $payment->names }} | {{ $payment->group_name }} |
@endforeach
@endcomponent
@endcomponent

@if($payments->count() > 100)
@component('mail::subcopy')
Se muestran únicamente los primeros 100 registros del reporte.
@endcomponent
@endif

@component('mail::subcopy')
Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos y marque este correo como no spam.
@endcomponent

Gracias,
# {{ config('app.name') }}
@endcomponent