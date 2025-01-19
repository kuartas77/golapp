<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CONTRATO DE INSCRIPCIÓN</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>
    @include('templates.contracts.header')
    @include('templates.contracts.footer')

    <h3>CONTRATO DE INSCRIPCION {{ strtoupper($school->name) }}</h3>

    <p>Nuestra institución fundamenta su funcionamiento en valores y normas que deben ser cumplidas y aceptadas, adquiriendo el compromiso que implica hacer parte de esta familia <strong>{{ strtoupper($school->name) }}</strong>.
    Para tales fines, el suscrito <strong>{{$tutor['names']}}</strong> identificado (a) con cédula de ciudadanía N.º <strong>{{$tutor['identification_card']}}</strong>, actuando en nombre propio y mayor de edad, y en calidad de <strong>PADRE/MADRE, TUTOR O REPRESENTANTE LEGAL Y/O ACUDIENTE</strong>, del <strong>ALUMNO:</strong> <strong>{{$player->names}} {{$player->last_names}}</strong>, suscriben contrato para la formación deportiva de fútbol, el cual se regirá por las siguientes cláusulas:</p>

    <p><strong>PRIMERA:</strong> El objeto del presente contrato es alcanzar la suma de esfuerzos y perfeccionar habilidades con <strong>EL ALUMNO</strong>, para lograr una formación integral para la práctica deportiva, dentro de los lineamientos establecidos por el Reglamento del <strong>{{ strtoupper($school->name) }}</strong> y demás normas que regulen la práctica deportiva de Fútbol.</p>
    <p><strong>SEGUNDA:</strong> <strong>{{ strtoupper($school->name) }}</strong> tendrá instructores especializados que realizarán el acompañamiento deportivo adecuado, en las sesiones programadas, a las cuales accederá <strong>EL ALUMNO</strong> durante el término de VIGENCIA de este contrato y en el horario en que tenga disponibilidad <strong>{{ strtoupper($school->name) }}</strong>, limitándose siempre a las siguientes condiciones:</p>
    <ol>
        <li><strong>EL ALUMNO</strong> deberá encontrarse en absoluto paz y salvo para poder asistir a cualquier actividad y/o clase de <strong>{{ strtoupper($school->name) }}</strong>.</li>
        <li><strong>EL ALUMNO</strong> deberá portar en todas las clases el uniforme de <strong>{{ strtoupper($school->name) }}</strong> estándole rotundamente prohibido portar ropa deportiva que no corresponda a la indumentaria oficial y original de <strong>{{ strtoupper($school->name) }}</strong>.</li>
        <li><strong>EL ALUMNO</strong> renuncia irrevocablemente a las clases y/o actividades a las que no hubiere asistido, por cualquier causa, dentro de la VIGENCIA del presente contrato y renuncia a cualquier tipo de reclamación y ajuste en el pago de la mensualidad por voluntad propia por este concepto frente a <strong>{{ strtoupper($school->name) }}</strong>, en consecuencia, todos los planes se vencen en la fecha correspondiente a la VIGENCIA del presente contrato independientemente del número de clases que <strong>EL ALUMNO</strong> tome y no hay lugar a devoluciones de dinero por ningún concepto.</li>
        <li><strong>EL ALUMNO</strong> no podrá ceder ni transferir, en ningún caso el presente contrato, ni los derechos y obligaciones que el mismo le confiere.</li>
        <li><strong>EL ALUMNO</strong> se obliga a PRESENTAR todos los documentos de conocimiento médico (INCAPACIDADES) y estado físico con expedición y presentación no mayor a diez (10) días calendario para poder comenzar a agendar y asistir a las actividades y clases incluidas en el presente contrato.</li>
        <li><strong>EL ALUMNO</strong> acepta irrevocablemente que conoce y acepta el REGLAMENTO/MANUAL DE CONVIVENCIA de <strong>{{ strtoupper($school->name) }}</strong>, y que el incumplimiento del mismo, así como sus posteriores modificaciones, podrá ocasionar la cancelación unilateral del presente contrato, a juicio de <strong>{{ strtoupper($school->name) }}</strong> sin lugar a pago o devolución alguna a <strong>EL ALUMNO</strong>.</li>
        <li><strong>EL ALUMNO</strong> por intermedio de su representante, se obliga a mantener vigente, en todo momento, su afiliación a una EPS.</li>
        <li><strong>EL ALUMNO</strong> por intermedio de su representante, se obliga a participar en la actividad de la rifa anual que consiste en la venta de los bonos participativos el cual tendrá un premio de DINERO EN EFECTIVO, Su participación es requisito fundamental al momento de ingresar al deportista, dicha actividad corresponde al valor de la matrícula, deportista que participe tendrá un descuento para la matricula del próximo año y el que no participe deberá pagar un excedente de $150.000 para complementar el valor total de la matrícula, dichos fondos se utilizarán para sostener niños de bajos recursos que se becan cada año, para optimizar recursos de las actividades complementarias que se realizan durante el año y mejoras institucionales.</li>
    </ol>

    <pagebreak />

    <p><strong>TERCERA:</strong> El presente contrato obliga al acudiente responsable del menor, por todos y cada uno de los valores por concepto de mensualidad que se generen durante el año y el concepto de matrícula, de acuerdo con las tarifas establecidas, esto dependiendo de donde quede matriculado el menor.</p>

    <table class="table-full detail detail-lines">
        <thead>
            <tr class="tr-tit">
                <th colspan="4" class="text-center">
                    <p><strong class="bold">PROCESO DE MATRICULA 2025</strong></p>
                    <p><strong class="bold">Cancha Juanes De La Paz</strong></p>
                    <p><strong class="bold">Por Calle 98A, Cra. 65, Medellín, Antioquia</strong></p>
                    <p><strong class="bold"><a href="https://maps.app.goo.gl/8BkLkrFk6ukNwsN1A">Ubicación</a></strong></p>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center"></td>
                <td class="text-center"><strong class="bold">&nbsp;INCRIPCIÓN&nbsp;</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;MENSUALIDAD&nbsp;</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;DIAS Y HORARIOS DE ENTRENAMIENTOS&nbsp;</strong></td>
            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;DEPORTISTAS ESTIMULACION&nbsp;</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;200.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$65.000</strong></td>
                <td class="text-center" rowspan="2">
                    <p><strong>&nbsp;GRUPO 2 - 5 AÑOS</strong></p>
                    <p><strong>&nbsp;Horario</strong></p>
                    <p><strong>&nbsp;Sábado </strong></p>
                    <p><strong>&nbsp;8:00am – 9:30am</strong></p>

                    <p><strong>&nbsp;Domingo </strong></p>
                    <p><strong>&nbsp;9:00am – 10:00am</strong></p>
                </td>
            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;DEPORTISTAS UN DIA DE ENTRANAMIENTO ESTIMULACION</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;190.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$55.000</strong></td>

            </tr>
        </tbody>
    </table>

    <pre></pre>

    <table class="table-full detail detail-lines">
        <thead>
            <tr class="tr-tit">
                <th colspan="4" class="text-center">
                    <p><strong class="bold">PROCESO DE MATRICULA 2025</strong></p>
                    <p><strong class="bold">Unidad Deportiva de Rene Higuita</strong></p>
                    <p><strong class="bold">Carrera 69 #101-25, Girardot, Medellín, Castilla, Medellín, Antioquia</strong></p>
                    <p><strong class="bold"><a href="https://maps.app.goo.gl/TGUAc3Rmyfrvrvk67">Ubicación</a></strong></p>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center"></td>
                <td class="text-center"><strong class="bold">&nbsp;INCRIPCIÓN&nbsp;</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;MENSUALIDAD&nbsp;</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;DIAS Y HORARIOS DE ENTRENAMIENTOS&nbsp;</strong></td>
            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;DEPORTISTAS ANTIGUOS&nbsp;</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;$335.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$90.000</strong></td>
                <td class="text-center" rowspan="2">
                    <p><strong>&nbsp;GRUPO 6 - 13 AÑOS</strong></p>
                    <p><strong>&nbsp;Horario</strong></p>
                    <p><strong>&nbsp;Miércoles y Viernes</strong></p>
                    <p><strong>&nbsp;6:00pm – 7:30pm</strong></p>
                </td>
            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;DEPORTISTAS NUEVOS</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;$355.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$90.000</strong></td>

            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;FEMENINO&nbsp;</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;255.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$50.000</strong></td>
                <td class="text-center">
                    <p><strong>&nbsp;GRUPO 6 - 15 AÑOS</strong></p>
                    <p><strong>&nbsp;Horario</strong></p>
                    <p><strong>&nbsp;Miércoles y Viernes</strong></p>
                    <p><strong>&nbsp;6:00pm – 7:30pm</strong></p>
                </td>
            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;DEPORTISTAS ESTIMULACION&nbsp;</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;$205.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$70.000</strong></td>
                <td class="text-center" rowspan="2">
                    <p><strong>&nbsp;GRUPO 2 - 5 AÑOS</strong></p>
                    <p><strong>&nbsp;Horario</strong></p>
                    <p><strong>&nbsp;Miércoles y Viernes</strong></p>
                    <p><strong>&nbsp;6:00pm – 7:30pm</strong></p>
                </td>
            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;DEPORTISTAS UN DIA DE ENTRANAMIENTO ESTIMULACION</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;190.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$55.000</strong></td>
            </tr>
        </tbody>
    </table>

    <pre></pre>

    <table class="table-full detail detail-lines">
        <thead>
            <tr class="tr-tit">
                <th colspan="4" class="text-center">
                    <p><strong class="bold">PROCESO DE MATRICULA 2025</strong></p>
                    <p><strong class="bold">Cancha Boyacá Las Brisas</strong></p>
                    <p><strong class="bold">Cra. 65 #112a-2, Medellín, Antioquia</strong></p>
                    <p><strong class="bold"><a href="https://maps.app.goo.gl/QeGUK5SAnjJzR8LM8">Ubicación</a></strong></p>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center"></td>
                <td class="text-center"><strong class="bold">&nbsp;INCRIPCIÓN&nbsp;</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;MENSUALIDAD&nbsp;</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;DIAS Y HORARIOS DE ENTRENAMIENTOS&nbsp;</strong></td>
            </tr>
            <tr>
                <td class="text-center"><strong class="bold">&nbsp;DEPORTISTAS ESTIMULACION&nbsp;</strong> </td>
                <td class="text-center"><strong class="bold">&nbsp;$275.000</strong></td>
                <td class="text-center"><strong class="bold">&nbsp;$50.000</strong></td>
                <td class="text-center">
                    <p><strong>&nbsp;GRUPOS</strong></p>
                    <p><strong>&nbsp;Horario</strong></p>
                    <p><strong>&nbsp;Martes y Viernes </strong></p>
                    <p><strong>&nbsp;8:00am – 9:30am</strong></p>
                    <p><strong>&nbsp;(2016-2015-2014-2013-2012)</strong></p>
                    <pre></pre>
                    <p><strong>&nbsp;Martes y Viernes </strong></p>
                    <p><strong>&nbsp;4:00pm – 05:00pm</strong></p>
                    <p><strong>&nbsp;(2011-2010-2009-2008)</strong></p>
                </td>
            </tr>
        </tbody>
    </table>

    <p><strong>PARÁGRAFO PRIMERO:</strong> El paquete de inscripción de afiliación a <strong>{{ strtoupper($school->name) }}</strong>, tendrán incluido lo siguiente:</p>

    <ul>
        <li>Inscripción</li>
        <li>Valoración Medica</li>
        <li>Valoración Deportiva (Test)</li>
        <li>Sostenimiento de la primera mensualidad</li>
        <li>Uniformes de entrenamiento</li>
        <li>Póliza contra accidentes</li>
    </ul>

    <p><strong>PARÁGRAFO SEGUNDO:</strong> El club se reserva el derecho (SUSPENSIÓN DE ENTRENAMIENTOS) en caso de acumulación de dos mensualidades, en caso de incumplimiento periódico o total de las mensualidades anteriormente mencionadas y/o cualquier otro compromiso y podrá hacer los cobros de manera directa o por intermedio de apoderado cuando sea el caso.</p>

    <pagebreak />

    <p><strong>CUARTA:</strong> <strong>EL ALUMNO</strong> por intermedio de su representante, conoce y acepta que los pagos se deberán realizar siempre en los cinco (5) primeros días de cada mes, si no se ha cancelado dentro de los cinco (5) primeros días del mes, el pago tendrá un recargo del 5%. A partir del día dieciséis (16 DEL MES), si EL <strong>ALUMNO</strong> no está a paz y salvo, no podrá ingresar a los entrenamientos, ni participar en ningún torneo, así esté inscrito.</p>
    <p><strong>QUINTA:</strong> Si por alguna circunstancia <strong>EL ALUMNO</strong> y en especial por lesión con certificado médico, decide desistir del servicio de la ESCUELA de formación antes de su inicio el club NO realiza devolución del valor cancelado y/o después de iniciar entrenamientos cualquiera que sea la justa causa de su desistimiento, deberá notificarlo por escrito a <strong>{{ strtoupper($school->name) }}</strong> para su respectiva cancelación de matrícula, si NO  se notifica dicho retiro del deportista por escrito al siguiente correo infsoccercity@gmail.com, las mensualidades contaran con normalidad lo cual deberá ser cancelado en su totalidad.</p>
    <p><strong>SEXTA:</strong> Exoneración De Responsabilidad Y Autorización Deportiva: <strong>EL ALUMNO</strong> por intermedio de su representante, mediante la firma de la presente, suministramos nuestro expreso consentimiento y autorización para que nuestro hijo(a) o representado, participe en torneos y salidas que organiza <strong>{{ strtoupper($school->name) }}</strong> en los siguientes términos:</p>

    <ol>
        <li>Autorizamos la participación del alumno(a) en los torneos o eventos que se lleve a cabo. Mediante la presente manifestamos la exoneración a <strong>{{ strtoupper($school->name) }}</strong>, de cualquier tipo de responsabilidad en caso de que el menor o el acudiente decida por voluntad propia no acudir al referido programa, no se realizará ningún tipo de reembolso ya que todos los abonos fueron depositados para cada evento previsto en el programa.</li>
        <li>Libero a <strong>{{ strtoupper($school->name) }}</strong>, a sus propietarios, empleados, voluntarios y todas las personas que son asociadas con el club de cualquier demanda, reclamo, entre otras acciones legales. Libero y eximo de toda responsabilidad e indemnización al club en caso de que mi hijo(a) sufra algún accidente, lesión o muerte, ya que conozco los riesgos inherentes a las actividades que se desarrollan en el club, como caídas, colisiones con otros alumnos, contacto con superficies duras en posiciones incorrectas, pero de igual forma pueden existir otros peligros conocidos o desconocidos en las instalaciones donde se realizan las prácticas, eventos o competencias deportivas.</li>
        <li>Declaro liberar a <strong>{{ strtoupper($school->name) }}</strong> y a todos los que con él se relacionan a cualquier reclamo que se realice al club con respecto a todas las obligaciones, pérdidas, daños, perjuicios, costos y gastos.</li>
        <li>Declaro haber leído, comprendido y aceptado todas las condiciones existentes, referentes entre otras políticas de cancelación, modificación y devolución de dineros.</li>
        <li>Declaro que me he informado, leído y comprendido el contrato de afiliación para que mi hijo(a) y/o representado se proponga a realizar las actividades deportivas establecidas, estando de acuerdo con los términos y condiciones.</li>
        <li>Certifico que toda la información proporcionada es real, que mi hijo(a) es beneficiario y está afiliado al régimen de salud (EPS) y que se encuentra activo actualmente.</li>
        <li>Exoneramos de toda obligación responsabilidad civil extracontractual, o contractual, a <strong>{{ strtoupper($school->name) }}</strong>, cuando las conductas de los participantes que sean realizadas bajo sustancias psicoactivas, depresoras: Alcohol, Anestésicos, Disolventes y sustancias volátiles (Inhalantes). Sustancias estimulantes, Sustancias alucinógenas.</li>
    </ol>

    <p><strong>SEPTIMA:</strong> Autorización Datos Personales Y Uso De Información. Con la suscripción del presente contrato <strong>EL ALUMNO</strong> y su representante, aceptan y manifiestan que autoriza a <strong>{{ strtoupper($school->name) }}</strong> para la recolección y tratamiento de sus datos personales, los cuales serán incorporados a una base de datos de la empresa, y serán utilizados para las finalidades consagradas en nuestra Política de Tratamiento de Datos Personales. <strong>{{ strtoupper($school->name) }}</strong> es el responsable del tratamiento de los datos personales. Declaro que los datos suministrados son veraces, exactos, auténticos y vigentes y en todo caso el suscrito será el único responsable de las manifestaciones falsas o inexactas que realice y de los eventuales perjuicios que cause a <strong>{{ strtoupper($school->name) }}</strong> o a terceros, por la información que facilité. Igualmente, declaro conocer mis derechos y los del menor como titular de la información y los procedimientos establecidos por <strong>{{ strtoupper($school->name) }}</strong> para el manejo y protección de datos personales que se encuentran en su Política de Protección de Datos Personales la cual está publicada en la página oficial</p>
    <p><strong>OCTAVA:</strong> Autorización Uso De Imagen. <strong>EL ALUMNO</strong> y su representante, con la firma del presente documento, otorga autorización expresa del uso de los derechos de imagen del menor que me reconocen la Constitución, la ley y demás normas concordantes a <strong>{{ strtoupper($school->name) }}</strong> , para que haga el uso y tratamiento de los derechos de imagen, para incluirlos sobre fotografías en su labor de captura, producción y coproducción de vídeos y fotografías en servicios del CLUB y los incluya en cualquier soporte audiovisual de reproducción y comunicación pública como redes sociales y páginas, entrevistas y fotografías realizadas al menor inscrito a los cursos, programas y equipos, así como utilizar su imagen en proyectos establecidos por CLUB. La autorización que aquí se concede sobre este material tendrá uso de carácter promocional publicitario y comercial para promoción de los diferentes servicios de <strong>{{ strtoupper($school->name) }}</strong> y será difundido por diferentes medios impresos o audiovisuales, con fines comerciales por los sistemas de televisión abierta o cerrada en el territorio nacional o en el exterior; así como de los Derechos de Autor, los Derechos Conexos y en general todos aquellos derechos de propiedad intelectual que tengan que ver con el derecho de imagen. La publicación podrá efectuarse de manera directa o a través de un tercero que se designe para tal fin.</p>

    <pagebreak />

    <p><strong>NOVENA:</strong> Expresamente se manifiesta que <strong>EL ALUMNO</strong>, goza de una perfecta salud física y mental, siendo como se indica veraz y completa la información médica facilitada, manifestando en tal sentido que, aún en el caso de padecer la enfermedad o disminución señalada en el apartado correspondiente de la inscripción, el menor se halla en condiciones psicofísicas idóneas para practicar y desarrollar con normalidad las actividades en las que se inscribe y que a tal efecto declaro conocer, haciendo constar que como participante inscrito <strong>EL ALUMNO</strong> se compromete a obedecer todas las instrucciones e indicaciones que los entrenadores, monitores, guías o instructores de las actividades concretas le faciliten, y muy especialmente aquellas derivadas de su específica situación física o psíquica.</p>
    <p><strong>DÉCIMA:</strong> El presente contrato presta Mérito Ejecutivo, de acuerdo con la Legislación Comercial Vigente, para tales efectos <strong>EL ALUMNO</strong> y su REPRESENTANTE aceptan y autorizan a <strong>{{ strtoupper($school->name) }}</strong> para cuando lo considere necesario, consulte y en caso de mora en las obligaciones, reporte a las Instituciones o Centrales de Información financiera, los datos y la información que considere necesaria y hacer el cobro Perjudico o jurídico al que haya lugar.</p>
    <p><strong>DÉCIMO PRIMERA:</strong> El presente contrato terminará, además de las razones contempladas en la Ley, y en el reglamento de <strong>{{ strtoupper($school->name) }}</strong> por:</p>
    <ul>
        <li><strong>a)</strong> Por cumplimiento de la temporada.</li>
        <li><strong>b)</strong> Por acuerdo escrito entre las partes.</li>
        <li><strong>c)</strong> Por muerte del <strong>ALUMNO</strong> o por clausura definitiva de la escuela.</li>
        <li><strong>d)</strong> Las que se deriven de la naturaleza jurídica del presente contrato.</li>
    </ul>
    <p><strong>DÉCIMO SEGUNDA:</strong> Las partes de mutuo acuerdo establecen que podrán notificar de manera electrónica los actos y/o decisiones que se profieran producto de la inscripción a <strong>{{ strtoupper($school->name) }}</strong>, para tales efectos, la notificación quedará surtida a partir de la fecha y hora en que <strong>EL ALUMNO</strong> tenga acceso, es decir, que el mismo quede disponible en la bandeja de entrada del correo electrónico informado por <strong>EL ALUMNO</strong> y/o su representante.</p>
    <p><strong>DECIMO TERCERA:</strong> Teniendo en cuenta el carácter de Formación que tiene la Escuela, el alumno deberá presentar fotocopia de sus informes académicos trimestralmente, para hacer seguimiento de los mismos.</p>
    <p><strong>DÉCIMA CUARTA:</strong> En caso de que se presente un caso fortuito o fuerza mayor, <strong>{{ strtoupper($school->name) }}</strong> no se encuentra obligado a reponer la clase, toda vez que la misma es producto de un hecho que es ajeno a su órbita de acción. Solo en época de invierno la realización de la práctica se dificulta, por lo tanto, se cambia el lugar de entrenamiento y hora.</p>
    <p><strong>DÉCIMA QUINTA:</strong> Cada cláusula de este contrato es válida en sí misma y no invalidará el resto. La cláusula inválida o incompleta podrá ser sustituida por otra equivalente y en caso de controversia o nulidad de una de las cláusulas contenidas en el presente convenio la misma NO invalidará o anulará las demás, por tanto, tendrán plena validez y exigencia.</p>

    <p>Para los efectos señalados el suscrito acepta las condiciones establecidas en el presente contrato de manera expresa, libre y voluntaria, exenta de vicios del consentimiento, habiendo comprendido el contenido y sus anexos, quien en señal de aceptación lo suscribe en la Ciudad de Medellín, al día {{now()->format('d')}} del mes de {{config('variables.KEY_MONTHS_INDEX')[now()->month]}} del año {{now()->format('Y')}}.</p>

    <pre></pre>
    <p>Firma de los contratantes,</p>
    <pre></pre>


    <table class="table-full title">
        <tr>
            <td class="text-center" width="45%"></td>
            <td class="text-center" width="10%"></td>
            <td class="text-center" width="45%">
            </td>
        </tr>
        <tr>
            <td class="text-left" width="45%">
                <img src="{{ storage_path('app/public/'.$school->sign)}}" width="180" height="120">
                <p><strong>{{ strtoupper($school->name) }}</strong></p>
            </td>
            <td class="text-center" width="10%"></td>
            <td class="text-left" width="45%">
                <img src="{{ storage_path('app/public/'.$sign_tutor)}}" width="140" height="120">
                <p><strong>Firma:&nbsp;&nbsp;&nbsp;</strong></p>
                <p><strong>Nombre:&nbsp;&nbsp;&nbsp;{{$tutor['names']}}</strong></p>
                <p><strong>ACUDIENTE Y/O RESPONSABLE DEL MENOR</strong></p>
            </td>
        </tr>

    </table>


</body>

</html>