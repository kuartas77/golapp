<h3>Ficha Del Deportista</h3>

<table class="table-full detail detail-lines">
    <tbody>
        <tr>
            <td class=""><strong class="bold">&nbsp;NOMBRE DEL DEPORTISTA:</strong> </td>
            <td class="" colspan="3">&nbsp;{{ $player->full_names }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;DOC. IDENTIDAD:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $player->identification_document }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;FECHA NACIMIENTO:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $player->date_birth }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;CATEGORIA:</strong></td>
            <td class="">&nbsp;{{ $player->category }}</td>
            <td class="">&nbsp;DÍAS DE ENTRENAMIENTO</td>
            <td class="">&nbsp;Lunes, Marter, Miércoles</td>
        </tr>
        @foreach($player->people as $people)
        <tr>
            <td class="">
                <strong class="bold">&nbsp;
                    @if($people->tutor)
                    ACUDIENTE
                    @else
                    {{config('variables.KEY_RELATIONSHIPS_SELECT')[$people->relationship]}}:
                    @endif
                </strong>
            </td>
            <td class="" colspan="3">&nbsp;{{ $people->names }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;TELÉFONO CELULAR:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $people->mobile }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;E-MAIL:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $people->email }}</td>
        </tr>
        @endforeach
        <!-- <tr>
            <td class=""><strong class="bold">&nbsp;NOMBRE DE LA MADRE:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $player->date_birth }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;TELÉFONO CELULAR:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $player->date_birth }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;E-MAIL:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $player->date_birth }}</td>
        </tr> -->

        <tr>
            <td class=""><strong class="bold">&nbsp;DIRECCIÓN:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $player->address }}</td>
        </tr>

        <tr>
            <td class=""><strong class="bold">&nbsp;EPS:</strong></td>
            <td class="" colspan="3">&nbsp;{{ $player->eps }}</td>
        </tr>
        <tr>
            <td class=""><strong class="bold">&nbsp;SEDE:</strong></td>
            <td class="" colspan="3">&nbsp;Belén las violetas</td>
        </tr>

    </tbody>
</table>
<pre></pre>
<pre></pre>
<pre></pre>
<pre></pre>
<pre></pre>
<pre></pre>
<h3>Términos y Condiciones</h3>

<p>
    La información administrativa o deportiva relacionada con la <strong>Academia 10+Pro</strong> sede Violetas será suministrada directamente <strong>María Teresa Zuluaga Mesa</strong> en los contactos:
    <ul>
        <li><strong>celular: 3045567698</strong></li>
        <li><strong>correo: matezuluaga@hotmail.com</strong></li>
    </ul>
</p>
<p>
    La <strong>Academia 10+Pro</strong> realizará sus operaciones deportivas los días martes y jueves 5:30 pm a 7:00 pm y los lunes, miércoles y viernes 7:00 pm 8:30 pm según la categoría en la que se encuentre, (la información es dada por cada profe de las categorías ) para la categoría de kids
    tendrán una duración de 1 hora dada la especificidad de la sesión los días martes y jueves 6:00 a 7:00 pm
    Las clases inician el 14 de enero de 2025, los pagos se realizarán por medio de transferencia a la <strong>cuenta N 00780027541, ahorros Bancolombia a nombre María Teresa Zuluaga Mesa, o en efectivo en la oficina del Club, los primeros 8 días del mes en curso.</strong>
</p>
<p>
    El valor de la matricula tiene un costo de 50.000 y La mensualidad tiene un valor de $70.000 mensuales, independientemente del número de sesiones a las que asista el usuario en el mes. (es importante tener en cuenta que se cancela hasta
    <strong>el mes de DICIEMBRE, si por alguna razón no puede asistir en ese mes debe enviar la información un mes antes o si es un caso extremo 15 días, para así no cobrarle el mes y evitarle inconvenientes)</strong>
</p>
<p>
    Después de recordar a los acudientes en máximo 2 oportunidades el compromiso adquirido con el pago de la mensualidad, y luego de presentar mora de máximo 10 días en el no pago de la misma, se podrá tener derivación o consecuencia en:

    <ul>
        <li>No se permitirá el ingreso del usurario deportista a las sesiones de la academia.</li>
        <li>El usuario deportista no podrá participar de torneos y competencias.</li>
    </ul>
</p>

<pagebreak />

<p>
    Los usuarios podrán adquirir la implementación deportiva por medio de <strong>María Teresa Zuluaga Mesa</strong>, Coordinadora de la Academia, consta de 2 Uniformes, uno de entrenamiento y otro de competencia. El valor será pagado de forma directa a la <strong>Academia 10+Pro</strong> por medio de transferencia electrónica a la cuenta de ahorros Bancolombia N° 00780027541, ahorros Bancolombia a nombre <strong>María Teresa Zuluaga Mesa</strong>, o en efectivo en la oficina del Club si así lo desea.
</p>

<p>
    La participación a los diferentes torneos tendrá un costo adicional a la mensualidad, el precio de la inscripción se dividirá teniendo en cuenta el valor total del torneo dividido en el número de integrantes de la respectiva categoría.
</p>

<p>
    Si el usuario presenta algún tipo de incapacidad médica y la misma tiene una duración que supere los 15 días, se deberá de informar dicha novedad vía e mail a la dirección electrónica <strong>matezuluaga@hotmail.com</strong> adjuntando copia de la incapacidad, de lo contrario no se suspenderá el cobro de la mensualidad. O también vía Whatssapp al numero <strong>3045567698</strong>.

    Si el usuario desea retirarse de la Academia deberá enviar la solicitud de retiro al correo electrónico <strong>matezuluaga@hotmail.com</strong> o avisar vía Whatsapp al numero <strong>3045567698</strong>, se hace importante mencionar que, en caso de no cumplir con este requisito, se continuará haciendo el cobro de la mensualidad de forma regular.

    La academia no se hace responsable por accidentes que se presenten en los entrenamientos y a su vez por los objetos perdidos.
</p>

<p>
    Acepto términos y condiciones, adicional dejo constancia que he firmado previamente el consentimiento informado de responsabilidad y cumplimiento de protocolos de bioseguridad para realización de práctica deportiva, establecido por <strong>el Club 10+Pro</strong>
</p>

<table class="table-full title">
    <tr>
        <td class="text-left" width="49%"><img src="{{ storage_path('app/public/'.$school->slug.'/firma10+pro.jpg')}}"></td>
        <td class="text-center" width="2%"></td>
        <td class="text-left" width="49%"><img src="{{ storage_path('app/public/'.$sign_tutor)}}" width="140" height="120"></td>
    </tr>
    <tr>
        <td class="text-left" width="49%">
            <p><strong>{{ $school->agent }}</strong></p>
            <p><strong>10+Pro</strong></p>
            <p><strong>&nbsp;&nbsp;&nbsp;</strong></p>
            <p><strong>&nbsp;&nbsp;&nbsp;</strong></p>
        </td>
        <td class="text-center" width="2%"></td>
        <td class="text-left" width="49%">
            <p><strong>Nombre:&nbsp;&nbsp;&nbsp;{{$tutor['names']}}</strong></p>
            <p><strong>Documento:&nbsp;&nbsp;&nbsp;{{$tutor['identification_card']}}</strong></p>
            <p><strong>ACUDIENTE Y/O RESPONSABLE DEL MENOR</strong></p>
            <p><strong>FECHA: {{ now()->format('d-m-Y') }}</strong></p>
        </td>
    </tr>
</table>