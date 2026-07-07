export const monthlyPaymentsTutorial = {
    steps: [
        {
            id: 'monthly-payments-filters',
            selector: '[data-tour="monthly-payments-filters"]',
            title: 'Empieza por los filtros',
            text: 'Selecciona el ano. Para el ano actual agrega grupo o categoria; en anos anteriores puedes consultar solo por ano y luego refinar los resultados.',
        },
        {
            id: 'monthly-payments-summary',
            selector: '[data-tour="monthly-payments-summary"]',
            title: 'Lee los totales y exporta',
            text: 'Este bloque resume montos de la pagina y habilita exportacion en PDF o Excel cuando aplique.',
        },
        {
            id: 'monthly-payments-table',
            selector: '[data-tour="monthly-payments-table"]',
            title: 'Gestiona el estado mes a mes',
            text: 'La tabla te deja revisar cada mes del deportista y editar celdas permitidas.',
        },
    ],
}
