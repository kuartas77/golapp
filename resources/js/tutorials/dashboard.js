export const homeTutorial = {
    steps: [
        {
            id: 'home-welcome',
            selector: '[data-tour="home-welcome"]',
            title: 'Empieza desde el dashboard',
            text: 'Esta tarjeta resume para que sirve GolApp y te deja saltar al modulo que mas uses.',
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
            title: 'Ubica los modulos principales',
            text: 'Este bloque explica que hace cada modulo del backoffice y a donde te lleva.',
        },
        {
            id: 'home-journey',
            selector: '[data-tour="home-journey"]',
            title: 'Sigue el flujo sugerido',
            text: 'Si alguien es nuevo en la plataforma, este panel resume el recorrido sugerido y concentra el flujo habitual de trabajo.',
        },
        {
            id: 'home-quick-links',
            selector: '[data-tour="home-quick-links"]',
            title: 'Usa los accesos rapidos',
            text: 'Debajo del recorrido sugerido tienes entradas directas a los procesos que mas se consultan en la operacion diaria.',
        },
    ],
}

export const kpiTutorial = {
    steps: [
        {
            id: 'kpi-toolbar',
            selector: '[data-tour="kpi-toolbar"]',
            title: 'Lee el panel de indicadores',
            text: 'Esta vista consolida metricas clave para que revises pagos y asistencia sin entrar a cada modulo.',
        },
        {
            id: 'kpi-payment-groups',
            selector: '[data-tour="kpi-payment-groups"]',
            title: 'Compara mensualidades por grupo',
            text: 'El primer grafico muestra el comportamiento de pagos por grupo durante el ano.',
        },
        {
            id: 'kpi-collection',
            selector: '[data-tour="kpi-collection"]',
            title: 'Revisa el recaudo',
            text: 'Este grafico cruza montos recaudados y porcentaje de cumplimiento para detectar variaciones.',
        },
        {
            id: 'kpi-attendance',
            selector: '[data-tour="kpi-attendance"]',
            title: 'Controla la asistencia',
            text: 'Aqui ves una lectura rapida de la asistencia de los grupos en el mes actual.',
        },
    ],
}
