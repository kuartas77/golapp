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
