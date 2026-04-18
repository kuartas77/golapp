export const playerStatsRankingTutorial = {
    steps: [
        {
            id: 'player-stats-ranking-actions',
            selector: '[data-tour="player-stats-ranking-actions"]',
            title: 'Muevete entre vistas',
            text: 'Desde esta cabecera puedes alternar entre ranking general y jugadores destacados.',
        },
        {
            id: 'player-stats-ranking-filters',
            selector: '[data-tour="player-stats-ranking-filters"]',
            title: 'Filtra el ranking',
            text: 'Usa posicion y categoria para quedarte solo con el grupo de jugadores que quieres revisar.',
        },
        {
            id: 'player-stats-ranking-rules',
            selector: '[data-tour="player-stats-ranking-rules"]',
            title: 'Entiende el puntaje',
            text: 'Este bloque resume las reglas que alimentan el escalafon del jugador.',
        },
        {
            id: 'player-stats-ranking-summary',
            selector: '[data-tour="player-stats-ranking-summary"]',
            title: 'Lee el resumen rapido',
            text: 'Aqui ves cantidad de jugadores, lider actual y si tienes filtros activos.',
        },
        {
            id: 'player-stats-ranking-table',
            selector: '[data-tour="player-stats-ranking-table"]',
            title: 'Analiza la tabla principal',
            text: 'La tabla muestra el detalle estadistico y te permite abrir el detalle individual del jugador.',
        },
    ],
}

export const topPlayersTutorial = {
    steps: [
        {
            id: 'top-players-actions',
            selector: '[data-tour="top-players-actions"]',
            title: 'Alterna entre destacados y ranking',
            text: 'Desde aqui puedes volver al ranking general o recargar la informacion del modulo.',
        },
        {
            id: 'top-players-spotlight',
            selector: '[data-tour="top-players-spotlight"]',
            title: 'Revisa el jugador foco',
            text: 'Esta tarjeta destaca rapidamente al jugador mas visible segun el rendimiento reciente.',
        },
        {
            id: 'top-players-scorers',
            selector: '[data-tour="top-players-scorers"]',
            title: 'Consulta los goleadores',
            text: 'El primer bloque ordena a los jugadores por goles y eficiencia ofensiva.',
        },
        {
            id: 'top-players-creation',
            selector: '[data-tour="top-players-creation"]',
            title: 'Compara creacion y defensa',
            text: 'Estos paneles separan a los mejores asistentes y a los porteros mas destacados.',
        },
        {
            id: 'top-players-rated',
            selector: '[data-tour="top-players-rated"]',
            title: 'Mira los mejor calificados',
            text: 'En esta seccion ves quienes sostienen mejores notas tecnicas dentro de la muestra.',
        },
    ],
}

export const playerStatsDetailTutorial = {
    steps: [
        {
            id: 'player-stats-detail-header',
            selector: '[data-tour="player-stats-detail-header"]',
            title: 'Consulta el perfil estadistico',
            text: 'La cabecera identifica al jugador y te permite volver al ranking o a los destacados.',
        },
        {
            id: 'player-stats-detail-metrics',
            selector: '[data-tour="player-stats-detail-metrics"]',
            title: 'Lee las metricas clave',
            text: 'Estas tarjetas resumen puntaje total, goles, asistencias y calificacion promedio.',
        },
        {
            id: 'player-stats-detail-summary',
            selector: '[data-tour="player-stats-detail-summary"]',
            title: 'Profundiza en el resumen',
            text: 'Aqui se concentra la participacion del jugador: minutos, titularidad, tarjetas y produccion.',
        },
        {
            id: 'player-stats-detail-matches',
            selector: '[data-tour="player-stats-detail-matches"]',
            title: 'Revisa el historial reciente',
            text: 'La tabla de ultimos partidos ayuda a entender el comportamiento mas actual del jugador.',
        },
        {
            id: 'player-stats-detail-positions',
            selector: '[data-tour="player-stats-detail-positions"]',
            title: 'Analiza las posiciones jugadas',
            text: 'Este grafico resume en que posiciones ha participado y con que frecuencia.',
        },
    ],
}
