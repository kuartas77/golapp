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
