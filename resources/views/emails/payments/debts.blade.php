@component('mail::message')
# Notificación pagos de mensualidades {{ $school->name }}.

@component('mail::panel')
## Mes(es) con novedades:
@if(in_array($payment->january, $index))
* Enero: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->january]}}
@endif
@if(in_array($payment->february, $index))
* Febrero: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->february]}}
@endif
@if(in_array($payment->march, $index))
* Marzo: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->march]}}
@endif
@if(in_array($payment->april, $index))
* Abril: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->april]}}
@endif
@if(in_array($payment->may, $index))
* Mayo: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->may]}}
@endif
@if(in_array($payment->june, $index))
* Junio: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->june]}}
@endif
@if(in_array($payment->july, $index))
* Julio: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->july]}}
@endif
@if(in_array($payment->august, $index))
* Agosto: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->august]}}
@endif
@if(in_array($payment->september, $index))
* Septiembre: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->september]}}
@endif
@if(in_array($payment->october, $index))
* Octubre: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->october]}}
@endif
@if(in_array($payment->november, $index))
* Noviembre: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->november]}}
@endif
@if(in_array($payment->december, $index))
* Diciembre: {{config('variables.KEY_PAYMENTS_SELECT')[$payment->december]}}
@endif
@endcomponent
## Esperamos que te pongas al día con las obligaciones.

@component('mail::subcopy')
    Para que futuros correos lleguen a su bandeja de entrada, por favor agréguenos a sus contactos.
@endcomponent

### ¡Este es un mensaje automático, por favor no responda!<br>
## Gracias,<br>
# {{ $school->name }}.
@endcomponent
