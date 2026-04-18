import { unref } from 'vue'

function goToPlayerStep(context, index) {
    const goToStep = unref(context.goToStep)

    if (typeof goToStep === 'function') {
        goToStep(index)
    }
}

export const playersListTutorial = {
    steps: [
        {
            id: 'players-list-intro',
            selector: '[data-tour="players-list-intro"]',
            title: 'Consulta el listado',
            text: 'Esta pagina concentra a todos los deportistas que han pasado por la escuela.',
        },
        {
            id: 'players-list-table',
            selector: '[data-tour="players-list-table"]',
            title: 'Abre el detalle del jugador',
            text: 'La tabla te permite buscar, ordenar y entrar a la ficha completa de cada deportista.',
        },
    ],
}

export const playerDetailTutorial = {
    steps: [
        {
            id: 'player-detail-wizard',
            selector: '[data-tour="player-detail-wizard"]',
            title: 'Diligencia la ficha por pasos',
            text: 'La informacion del deportista esta organizada en un wizard para no perder el hilo del registro.',
            beforeEnter: async ({ context }) => {
                goToPlayerStep(context, 0)
            },
        },
        {
            id: 'player-detail-personal',
            selector: '[data-tour="player-detail-personal"]',
            title: 'Completa la informacion personal',
            text: 'En este paso registras identidad, fecha de nacimiento, genero y datos basicos del jugador.',
            beforeEnter: async ({ context }) => {
                goToPlayerStep(context, 0)
            },
        },
        {
            id: 'player-detail-general',
            selector: '[data-tour="player-detail-general"]',
            title: 'Agrega la informacion general',
            text: 'Aqui capturas datos de contacto, salud, estudio y residencia del deportista.',
            beforeEnter: async ({ context }) => {
                goToPlayerStep(context, 1)
            },
        },
        {
            id: 'player-detail-family',
            selector: '[data-tour="player-detail-family"]',
            title: 'Registra el entorno familiar',
            text: 'Este ultimo bloque sirve para dejar parentescos, telefonos y responsables del jugador.',
            beforeEnter: async ({ context }) => {
                goToPlayerStep(context, 2)
            },
        },
    ],

    async onAfterClose({ context }) {
        goToPlayerStep(context, 0)
    },
}
