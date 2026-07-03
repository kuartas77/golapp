export const competitionStatsTutorial = {
    steps: [
        { id: 'competition-stats-actions', selector: '[data-tour="competition-stats-actions"]', title: 'Explora las estadisticas', text: 'Esta vista consolida el rendimiento de los grupos con partidos jugados.' },
        { id: 'competition-stats-filters', selector: '[data-tour="competition-stats-filters"]', title: 'Define la consulta', text: 'Filtra la informacion por ano, torneo y categoria.' },
        { id: 'competition-stats-summary', selector: '[data-tour="competition-stats-summary"]', title: 'Lee el resumen', text: 'Revisa partidos, balance de gol, rendimiento y distribucion de resultados.' },
        { id: 'competition-stats-ranking', selector: '[data-tour="competition-stats-ranking"]', title: 'Compara los grupos', text: 'El escalafon ordena los grupos y permite abrir su detalle competitivo.' },
    ],
}

export const tournamentsTutorial = {
    steps: [
        { id: 'admin-tournaments-actions', selector: '[data-tour="admin-tournaments-actions"]', title: 'Crea un torneo', text: 'Usa esta accion para registrar un torneo disponible para los grupos de competencia.' },
        { id: 'admin-tournaments-table', selector: '[data-tour="admin-tournaments-table"]', title: 'Administra los torneos', text: 'Consulta los torneos existentes y utiliza sus acciones de edicion o eliminacion.' },
    ],
}
