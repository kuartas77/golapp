export const homeTutorial = {
    steps: [
        {
            id: 'home-welcome',
            selector: '[data-tour="home-welcome"]',
            title: 'Empieza desde el dashboard',
            text: 'Esta tarjeta resume el flujo general de trabajo y te deja saltar al modulo que mas sentido tenga para tu jornada.',
        },
        {
            id: 'home-context',
            selector: '[data-tour="home-context"]',
            title: 'Valida tu contexto',
            text: 'Aqui confirmas usuario, perfil, escuela y alcance habilitado en esta sesion.',
        },
        {
            id: 'home-modules',
            selector: '[data-tour="home-modules"]',
            title: 'Revisa los modulos disponibles',
            text: 'Aqui tienes accesos compactos a cada modulo habilitado y un tooltip breve para entender su funcion.',
        },
        {
            id: 'home-journey',
            selector: '[data-tour="home-journey"]',
            title: 'Sigue el flujo recomendado',
            text: 'Este panel concentra el recorrido sugerido para usar la plataforma con un orden claro desde el primer ingreso.',
        },
        {
            id: 'home-next-step',
            selector: '[data-tour="home-next-step"]',
            title: 'Continua con el siguiente paso',
            text: 'Al final del recorrido veras la recomendacion mas directa para continuar segun los modulos disponibles para tu perfil.',
        },
    ],
}

export const kpiTutorial = {
    steps: [
        {
            id: 'kpi-toolbar',
            selector: '[data-tour="kpi-toolbar"]',
            title: 'Lee el tablero ejecutivo',
            text: 'Esta portada resume salud financiera y operativa para que detectes alertas sin entrar a cada modulo.',
        },
        {
            id: 'kpi-filters',
            selector: '[data-tour="kpi-filters"]',
            title: 'Filtra el corte',
            text: 'Define ano, mes y grupo para cambiar el contexto del tablero sin perder la vista general.',
        },
        {
            id: 'kpi-summary',
            selector: '[data-tour="kpi-summary"]',
            title: 'Empieza por los KPI',
            text: 'Estas tarjetas te dan una lectura rapida de recaudo, cumplimiento, asistencia y jugadores observados.',
        },
        {
            id: 'kpi-payment-groups',
            selector: '[data-tour="kpi-payment-groups"]',
            title: 'Compara mensualidades por grupo',
            text: 'Este grafico compara pagos, deuda, becados y otros estados por grupo durante el ano seleccionado.',
        },
        {
            id: 'kpi-collection',
            selector: '[data-tour="kpi-collection"]',
            title: 'Revisa el recaudo',
            text: 'Aqui cruzas montos recaudados e indicadores de cumplimiento para ubicar desviaciones por grupo.',
        },
        {
            id: 'kpi-monthly-trend',
            selector: '[data-tour="kpi-monthly-trend"]',
            title: 'Sigue la tendencia del ano',
            text: 'Observa si el recaudo y la cantidad de pagos mantienen el ritmo esperado mes a mes.',
        },
        {
            id: 'kpi-attendance',
            selector: '[data-tour="kpi-attendance"]',
            title: 'Controla la asistencia',
            text: 'Este bloque resume el reparto del mes entre asistencias, excusas, ausencias, retiros e incapacidades.',
        },
        {
            id: 'kpi-rankings',
            selector: '[data-tour="kpi-rankings"]',
            title: 'Prioriza la accion',
            text: 'Los rankings te ayudan a identificar rapido los grupos con mejor o peor comportamiento para actuar primero.',
        },
    ],
}
