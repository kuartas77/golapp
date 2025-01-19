<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
    <style>
        p {
            text-align: justify;
            text-justify: inter-word;
        }
    </style>
</head>
<body>
    @include('templates.contracts.header')
    @include('templates.contracts.footer')

    <h3>CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA DE {{ strtoupper($school->name) }}</h3>

    <h3>PREÁMBULO</h3>
    <p>El presente reglamento pretende crear un conjunto de criterios de organización de las actividades deportivas de <strong>{{ strtoupper($school->name) }}</strong>, Todas las personas que se integran, se comprometen a conocer y cumplir el presente reglamento de régimen interno. a título individual o bien a título de sus familiares.
    La creación de un ambiente agradable y disciplinado dentro del club deportivo es también una tarea que nos compete a todos y a la cual todos debemos de integrarnos. Ello nos permitirá un mayor nivel de satisfacción personal y colectivo, y también contribuirá sin duda a un mayor éxito deportivo.
    Por tanto, todos los jugadores están obligados al respeto que se establece en la presente normativa interna, sus deberes y funciones como padres de familia son las siguientes:
    </p>

    <h3>CAPÍTULO I</h3>
    <h3>DERECHOS</h3>
    <p>
    <p><strong>Artículo 1.</strong> Todos los deportistas tienen derecho a las mismas oportunidades de acceso a los distintos niveles deportivos, no habrá limitaciones si su rendimiento y aptitudes son óptimas para avanzar en su proceso deportivo.</p>
    <p><strong>Artículo 2.</strong> Asumir desde el primer momento las normas que se establezcan, fundamentalmente consideradas como ideales del club deportivo.</p>
    <p><strong>Parágrafo 1.</strong> Respeto a la libertad de conciencia y la dignidad, integridad e intimidad de todos los miembros.</p>
    <p><strong>Parágrafo 2.</strong> La no discriminación a ningún miembro por razón de nacimiento, raza, sexo o por cualquier otra circunstancia personal o social.</p>
    <p><strong>Artículo 3.</strong> Mantener en todo momento un trato amable y respetuoso y un comportamiento deportivo ejemplar hacia los profesores, los compañeros, los adversarios y padres de familia.</p>
    <p><strong>Artículo 4.</strong> Velar siempre por dejar una buena imagen del club en la formación integral y valores que están adquiriendo y tener conciencia que al participar con el equipo que fuere están actuando en nombre de <strong>{{ strtoupper($school->name) }}</strong> y procurar hacer honor a la confianza depositada en ellos.</p></p>

    <h3>CAPÍTULO II</h3>
    <h3>DEBERES</h3>
    <p><strong>Artículo 5.</strong> Asistir puntualmente a los entrenamientos, competencias y actividades organizadas por el club, para la práctica deportiva de que se trate, esforzándose y obligándose a aprender y mejorar en todos los aspectos deportivos y personales. </p>
    <p><strong>Artículo 6.</strong> Al momento de no poder asistir deberá comunicarlo al entrenador asignado o llamar a la oficina, si consecutivamente completa 3 faltas de asistencias sin justificación alguna, probablemente el entrenador pasará el respectivo informe se estudiará el caso y podrá ser retirado de dicha competencia.</p>
    <p><strong>Artículo 7.</strong> Es responsabilidad del deportista el día viernes de cada semana revisar todos los medios de comunicación que el club posee: página web, Facebook, grupos WhatsApp, teléfono fijo, celular de profesores, esto con el fin que esté informado de las actividades que se realizará el fin de semana, no hay excusa para decir que no sabía de dicha actividad.</p>
    <p><strong>Artículo 8.</strong> Acatar y respetar las órdenes del profesor y actuar en la posición asignada para la competencia, el fútbol es un deporte de conjunto donde prima el bien colectivo sobre el individual, en caso de alguna inconformidad debe acercarse al profesor respetuosamente y manifestarlo.</p>
    <p><strong>Artículo 9.</strong> Apoyar a sus hijos sin interferir con el trabajo de los formadores; entender que {{ strtoupper($school->name) }} tiene unas políticas institucionales y que como padres tienen todo el derecho a opinar y contribuir con el mejoramiento de los procesos. Más sobre una decisión técnica no tienen la autoridad para decir, quien tiene la autonomía y facultad para decidir es el formador encargado del grupo.</p>

    <pagebreak />

    <h3>CAPÍTULO III</h3>
    <h3>CONVIVENCIA CIUDADANA</h3>
    <p><strong>Artículo 10.</strong> Evitar hacer comentarios y recomendaciones técnico–tácticas a sus hijos tanto en los entrenamientos como en los partidos. Basta con impulsarlos y animarlos mediante, palabras, aplausos y gestos, todos siempre de apoyo y animo evitando al máximo cualquier actitud negativa.</p>
    <p><strong>Artículo 11.</strong> En el aspecto canchas (entrenamiento y partido), prohibido estar dentro de la cancha el espacio es exclusivo para los deportistas y formadores; abstenerse de sugerir u ordenar cualquier disposición técnica, táctica, física al formador o jugador.</p>
    <p><strong>Artículo 12.</strong> Cuidar y hacer buen uso del material deportivo y sus instalaciones haciendo un uso adecuado y correcto de los mismos a tal fin deberá comunicar a su entrenador cualquier anomalía sobre los mismos.</p>
    <p><strong>Artículo 13.</strong> Presentar la documentación necesaria solicitada por el club para actualizar la base de datos, también para la participación en torneos, actividades y diferentes eventos.</p>
    <p><strong>Artículo 14.</strong> Presentarse a todas las actividades con los uniformes respectivos de entrenamiento, de competencia, de presentación, morral, peto y balón. si el deportista no se presenta con la indumentaria correspondiente <strong>NO PODRÁ PARTICIPAR EN LA CLASE Y/O COMPETENCIA</strong> </p>
    <p><strong>Artículo 15.</strong> No se permitirá insultos ni actitudes agresivas entre compañeros, adversarios o cualquier miembro del cuerpo técnico ni en los entrenamientos ni en los partidos, en caso de producirse se considerará una falta grave y obligará a la apertura de un expediente disciplinario por parte de la dirección.</p>
    <p><strong>Artículo 16.</strong> La educación de nuestros jugadores es tarea de todos, padres de familia acudientes o quienes representen a menores inscritos, forman parte principal de nuestro proyecto deportivo, por ello son colaboradores imprescindibles en la enseñanza y respeto deportivo y a los valores que representan en el mismo; es responsabilidad de padres o acudientes al acompañar con puntualidad a los deportistas al comienzo de los entrenamientos o partidos hasta la entrada y la recogida de los deportistas a la finalización de los mismos a la misma zona.</p>
    <p><strong>Artículo 17.</strong> Ser porta voz hacia los padres de cualquier evento, partidos, reuniones y todo lo requerido.</p>

    <h3>CAPÍTULO IV</h3>
    <h3>CORRESPONSABILIDAD</h3>
    <p><strong>Artículo 18.</strong> Si un padre de familia es sorprendido alterando la armonía de otros padres de familia y de la organización, con comentarios mal intencionados para crear un conflicto interno y con una
    conducta inadecuada de irrespeto frente a los formadores, padres de familia o adversarios; en eventos o actividades, que el club realice tendrá un llamado de atención y si es reincidente será sancionado por la dirección general, dependiendo de la gravedad del asunto será estudiado por la comisión disciplinaria para la desafiliación del deportista y su familia. Recuerde que en el club no solo ingresa el deportista sino también la familia.</p>
    <p><strong>Artículo 19.</strong> Si en algún juego por razones administrativas no pudo asistir el formador del grupo deberá respetar y acatar las órdenes del formador de turno, este procurará hacerlo de la mejor manera.</p>
    <p><strong>Artículo 20.</strong> Los grupos del club se organizan con su respectiva población tanto en los procesos competitivos como en los procesos formativos y la cantidad de deportistas considerable para conformar cada grupo; quien acumule una cifra numerosa de inasistencias sin justa causa certificada, tanto en partidos amistosos o de torneo tendrá un llamado de atención y se estudiará el caso para su posible retiro del grupo de competencia.</p>

    <table class="table-full title">
        <tr>
            <td class="text-left" width="45%">
                <img src="{{ storage_path('app/public/'.$sign_tutor)}}" width="120" height="120">
                <p><strong>{{$tutor['names']}}</strong></p>
            </td>
            <td class="text-center" width="10%"></td>
            <td class="text-left" width="45%">
                <img src="{{ storage_path('app/public/'.$sign_player)}}" width="120" height="120">
                <p><strong>{{$player->names}} {{$player->last_names}}</strong></p>
            </td>
        </tr>
        <tr>
            <td class="text-left" width="45%">
                <strong>ACUDIENTE Y/O RESPONSABLE DEL MENOR</strong>
            </td>
            <td class="text-center" width="10%"></td>
            <td class="text-left" width="45%">
                <strong>Alumno</strong>
            </td>
        </tr>

    </table>


</body>

</html>