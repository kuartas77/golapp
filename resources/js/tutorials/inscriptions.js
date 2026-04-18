import { unref } from 'vue'

export const inscriptionsTutorial = {
    getSteps(context) {
        const steps = [
            {
                id: 'inscription-year-filter',
                selector: '[data-tour="inscription-year-filter"]',
                title: 'Filtra por ano',
                text: 'Desde aqui puedes consultar y administrar las inscripciones de un ano especifico.',
                tips: [
                    'Al cambiar el ano, la tabla se recarga automaticamente.',
                    'Si el ano no existe en la configuracion, se usa el mas reciente disponible.',
                ],
            },
            {
                id: 'inscription-group-filter',
                selector: '[data-tour="inscription-group-filter"]',
                title: 'Filtra por grupo',
                text: 'Este filtro te ayuda a quedarte solo con las inscripciones del grupo de entrenamiento que necesitas revisar.',
                tips: [
                    'Aplica directamente sobre la tabla.',
                    'Puedes combinarlo con los demas filtros de la vista.',
                ],
            },
            {
                id: 'inscription-category-filter',
                selector: '[data-tour="inscription-category-filter"]',
                title: 'Filtra por categoria',
                text: 'Usa este selector para segmentar rapidamente las inscripciones por categoria.',
                tips: [
                    'Es util para validaciones masivas.',
                    'Funciona junto al filtro de grupo y al ano seleccionado.',
                ],
            },
            {
                id: 'inscriptions-table',
                selector: '[data-tour="inscriptions-table"]',
                title: 'Revisa la tabla',
                text: 'Aqui ves el listado consolidado de inscripciones y puedes abrir sus acciones disponibles.',
                tips: [
                    'La tabla se actualiza segun los filtros aplicados.',
                    'Desde la ultima columna podras imprimir o gestionar cada inscripcion.',
                ],
            },
        ]

        if (unref(context.canExportInscriptions)) {
            steps.splice(1, 0, {
                id: 'inscription-export',
                selector: '[data-tour="inscription-export"]',
                title: 'Exporta la informacion',
                text: 'Si tienes permisos, puedes descargar el listado actual en Excel para revisiones o reportes.',
                tips: [
                    'El archivo respeta el ano que tengas seleccionado.',
                ],
            })
        }

        return steps
    },
}
