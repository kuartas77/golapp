import { unref } from 'vue'

export const matchesListTutorial = {
    steps: [
        {
            id: 'matches-list-actions',
            selector: '[data-tour="matches-list-actions"]',
            title: 'Crea una competencia',
            text: 'Desde este boton eliges el grupo de competencia y entras al formulario del partido.',
        },
        {
            id: 'matches-list-table',
            selector: '[data-tour="matches-list-table"]',
            title: 'Administra los partidos',
            text: 'La tabla muestra torneo, grupo, fecha, rival y acciones para editar o imprimir.',
        },
    ],
}

export const matchFormTutorial = {
    getSteps(context) {
        const isEdition = Boolean(unref(context.isEdition))
        const steps = [
            {
                id: 'match-form-header',
                selector: '[data-tour="match-form-header"]',
                title: 'Trabaja el formulario del partido',
                text: 'Aqui registras la informacion base del encuentro y luego guardas el control de competencia.',
            },
            {
                id: 'match-form-general',
                selector: '[data-tour="match-form-general"]',
                title: 'Completa los datos del encuentro',
                text: 'Define torneo, lugar, fecha, hora y rival antes de avanzar al resto del registro.',
            },
        ]

        if (isEdition) {
            steps.push(
                {
                    id: 'match-form-result',
                    selector: '[data-tour="match-form-result"]',
                    title: 'Registra el resultado final',
                    text: 'En modo edicion puedes cargar formato, marcar el marcador final y dejar concepto general.',
                },
                {
                    id: 'match-form-stats',
                    selector: '[data-tour="match-form-stats"]',
                    title: 'Diligencia las estadisticas por deportista',
                    text: 'Esta tabla captura asistencia, posicion, minutos, goles, tarjetas y observaciones del partido.',
                },
            )
        } else {
            steps.push({
                id: 'match-form-board',
                selector: '[data-tour="match-form-board"]',
                title: 'Organiza el tablero tecnico',
                text: 'En la creacion puedes acomodar el plantel en el tablero para preparar la competencia.',
            })
        }

        steps.push({
            id: 'match-form-actions',
            selector: '[data-tour="match-form-actions"]',
            title: 'Guarda el control',
            text: 'Cuando el registro este listo puedes descargar el formato o guardar los cambios.',
        })

        return steps
    },
}
