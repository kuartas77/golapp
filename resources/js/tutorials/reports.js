export const assistReportsTutorial = {
    steps: [
        {
            id: 'assist-reports-monthly-player',
            selector: '[data-tour="assist-reports-monthly-player"]',
            title: 'Consulta el reporte mensual por jugador',
            text: 'Este primer panel filtra por ano, mes y grupo para generar el consolidado individual.',
        },
        {
            id: 'assist-reports-monthly-group',
            selector: '[data-tour="assist-reports-monthly-group"]',
            title: 'Consulta el reporte mensual por grupo',
            text: 'Aqui revisas el comportamiento agregado de asistencia a nivel de grupo de entrenamiento.',
        },
        {
            id: 'assist-reports-annual',
            selector: '[data-tour="assist-reports-annual"]',
            title: 'Abre el consolidado anual',
            text: 'Este bloque resume la asistencia de todo el ano y permite filtrar por grupo cuando lo necesites.',
        },
    ],
}

export const paymentReportTutorial = {
    steps: [
        {
            id: 'payment-report-intro',
            selector: '[data-tour="payment-report-intro"]',
            title: 'Solicita el informe por correo',
            text: 'Este modulo genera el informe de pagos en segundo plano y lo envia al correo del usuario.',
        },
        {
            id: 'payment-report-filters',
            selector: '[data-tour="payment-report-filters"]',
            title: 'Selecciona ano y grupo',
            text: 'Puedes dejar el grupo vacio para pedir el consolidado completo del ano elegido.',
        },
        {
            id: 'payment-report-action',
            selector: '[data-tour="payment-report-action"]',
            title: 'Lanza la solicitud',
            text: 'Cuando el filtro este listo, usa este boton para enviar la generacion del reporte.',
        },
    ],
}

export const instructorActivityReportTutorial = {
    steps: [
        { id: 'instructor-activity-actions', selector: '[data-tour="instructor-activity-actions"]', title: 'Consulta y exporta actividad', text: 'La cabecera resume el informe y ofrece sus exportaciones.' },
        { id: 'instructor-activity-filters', selector: '[data-tour="instructor-activity-filters"]', title: 'Define el periodo', text: 'Selecciona ano, mes e instructor antes de consultar.' },
        { id: 'instructor-activity-table', selector: '[data-tour="instructor-activity-table"]', title: 'Compara la actividad', text: 'Revisa asistencias, competencias, metodologias y sesiones por instructor.' },
    ],
}

export const debtorReportTutorial = {
    steps: [
        { id: 'debtor-report-context', selector: '[data-tour="debtor-report-context"]', title: 'Prepara el informe de deudores', text: 'Este informe consolida deudas de mensualidades y facturas por deportista.' },
        { id: 'debtor-report-filters', selector: '[data-tour="debtor-report-filters"]', title: 'Selecciona el alcance', text: 'Define el ano, el grupo y el nivel de detalle que tendra el PDF.' },
        { id: 'debtor-report-actions', selector: '[data-tour="debtor-report-actions"]', title: 'Genera el documento', text: 'Exporta el informe con las opciones seleccionadas.' },
    ],
}

export const attendancePaymentReportTutorial = {
    steps: [
        { id: 'attendance-payment-context', selector: '[data-tour="attendance-payment-context"]', title: 'Identifica inconsistencias', text: 'Compara asistencias registradas con el estado de las mensualidades.' },
        { id: 'attendance-payment-filters', selector: '[data-tour="attendance-payment-filters"]', title: 'Define la consulta', text: 'Selecciona periodo y grupo para actualizar los resultados.' },
        { id: 'attendance-payment-summary', selector: '[data-tour="attendance-payment-summary"]', title: 'Revisa el resumen', text: 'Compara deportistas asistentes y observados por grupo.' },
        { id: 'attendance-payment-detail', selector: '[data-tour="attendance-payment-detail"]', title: 'Consulta el detalle', text: 'Revisa los deportistas con asistencia y mensualidad pendiente.' },
    ],
}
