import { unref } from 'vue'

export const playerEvaluationsIndexTutorial = {
    steps: [
        {
            id: 'player-evaluations-index-actions',
            selector: '[data-tour="player-evaluations-index-actions"]',
            title: 'Empieza por las acciones principales',
            text: 'Desde aqui puedes abrir el comparativo o crear una nueva evaluacion.',
        },
        {
            id: 'player-evaluations-index-filters',
            selector: '[data-tour="player-evaluations-index-filters"]',
            title: 'Filtra las evaluaciones',
            text: 'Busca por jugador, grupo, periodo, estado o tipo para ubicar el seguimiento que necesitas.',
        },
        {
            id: 'player-evaluations-index-summary',
            selector: '[data-tour="player-evaluations-index-summary"]',
            title: 'Lee el resumen de la pagina',
            text: 'Las tarjetas muestran total filtrado, pagina actual y rango visible del listado.',
        },
        {
            id: 'player-evaluations-index-table',
            selector: '[data-tour="player-evaluations-index-table"]',
            title: 'Gestiona desde la tabla',
            text: 'Aqui puedes abrir el detalle, editar si la evaluacion sigue abierta, exportar o eliminar.',
        },
    ],
}

export const playerEvaluationsComparisonTutorial = {
    steps: [
        {
            id: 'player-evaluations-comparison-actions',
            selector: '[data-tour="player-evaluations-comparison-actions"]',
            title: 'Abre o exporta el comparativo',
            text: 'Desde la cabecera vuelves al listado o exportas el informe en PDF cuando hay datos.',
        },
        {
            id: 'player-evaluations-comparison-filters',
            selector: '[data-tour="player-evaluations-comparison-filters"]',
            title: 'Selecciona jugador y periodos',
            text: 'El comparativo funciona eligiendo una inscripcion y dos periodos para contrastar.',
        },
        {
            id: 'player-evaluations-comparison-overall',
            selector: '[data-tour="player-evaluations-comparison-overall"]',
            title: 'Interpreta el resultado general',
            text: 'Este bloque resume si el jugador avanzo, retrocedio o se mantuvo entre ambos cortes.',
        },
        {
            id: 'player-evaluations-comparison-dimensions',
            selector: '[data-tour="player-evaluations-comparison-dimensions"]',
            title: 'Baja al nivel de dimensiones',
            text: 'La tabla por dimensiones muestra en que ejes hubo mayor cambio.',
        },
        {
            id: 'player-evaluations-comparison-criteria',
            selector: '[data-tour="player-evaluations-comparison-criteria"]',
            title: 'Revisa el detalle por criterio',
            text: 'Aqui comparas puntajes y comentarios criterio por criterio entre los dos periodos.',
        },
    ],
}

export const playerEvaluationsEditorTutorial = {
    getSteps(context) {
        const isEditMode = Boolean(unref(context.isEditMode))
        const formReady = Boolean(unref(context.formReady))

        if (!isEditMode && !formReady) {
            return [
                {
                    id: 'player-evaluations-editor-selection',
                    selector: '[data-tour="player-evaluations-editor-selection"]',
                    title: 'Configura la evaluacion',
                    text: 'Antes de diligenciar la evaluacion debes elegir inscripcion, periodo y plantilla.',
                },
                {
                    id: 'player-evaluations-editor-selection-help',
                    selector: '[data-tour="player-evaluations-editor-selection-help"]',
                    title: 'Confirma el contexto',
                    text: 'Estas ayudas resumen para que sirve cada selector y evitan iniciar con una combinacion incorrecta.',
                },
                {
                    id: 'player-evaluations-editor-selection-actions',
                    selector: '[data-tour="player-evaluations-editor-selection-actions"]',
                    title: 'Entra al formulario',
                    text: 'Cuando la seleccion este lista, continua al formulario para registrar los criterios.',
                },
            ]
        }

        return [
            {
                id: 'player-evaluations-editor-summary',
                selector: '[data-tour="player-evaluations-editor-summary"]',
                title: 'Valida el contexto activo',
                text: 'Estas tarjetas te recuerdan jugador, periodo, plantilla y avance del formulario.',
            },
            {
                id: 'player-evaluations-editor-config',
                selector: '[data-tour="player-evaluations-editor-config"]',
                title: 'Ajusta estado y tipo',
                text: 'Aqui defines la fecha, el tipo de evaluacion y el estado del registro.',
            },
            {
                id: 'player-evaluations-editor-progress',
                selector: '[data-tour="player-evaluations-editor-progress"]',
                title: 'Sigue el progreso',
                text: 'Este bloque muestra promedio preliminar, faltantes obligatorios y avance ponderado.',
            },
            {
                id: 'player-evaluations-editor-dimensions',
                selector: '[data-tour="player-evaluations-editor-dimensions"]',
                title: 'Diligencia por dimensiones',
                text: 'Abre cada dimension para asignar puntajes, comentarios y completar criterios obligatorios.',
            },
            {
                id: 'player-evaluations-editor-comments',
                selector: '[data-tour="player-evaluations-editor-comments"]',
                title: 'Completa la lectura final',
                text: 'Las conclusiones y recomendaciones cierran la evaluacion con un analisis global.',
            },
            {
                id: 'player-evaluations-editor-actions',
                selector: '[data-tour="player-evaluations-editor-actions"]',
                title: 'Guarda el seguimiento',
                text: 'Al final puedes cancelar o guardar la evaluacion segun el avance diligenciado.',
            },
        ]
    },
}

export const playerEvaluationsShowTutorial = {
    steps: [
        {
            id: 'player-evaluations-show-header',
            selector: '[data-tour="player-evaluations-show-header"]',
            title: 'Ubica la evaluacion',
            text: 'La cabecera te deja volver, editar si aplica o descargar el PDF del detalle.',
        },
        {
            id: 'player-evaluations-show-summary',
            selector: '[data-tour="player-evaluations-show-summary"]',
            title: 'Lee el resumen ejecutivo',
            text: 'Estas tarjetas muestran jugador, periodo, estado y nota general del registro.',
        },
        {
            id: 'player-evaluations-show-dimensions',
            selector: '[data-tour="player-evaluations-show-dimensions"]',
            title: 'Baja al detalle por dimensiones',
            text: 'Cada bloque agrupa criterios y comentarios dentro de una misma dimension tecnica.',
        },
        {
            id: 'player-evaluations-show-conclusions',
            selector: '[data-tour="player-evaluations-show-conclusions"]',
            title: 'Cierra con las conclusiones',
            text: 'Aqui se concentran fortalezas, oportunidades y recomendaciones del seguimiento.',
        },
    ],
}
