import { nextTick, unref } from 'vue'

const CLASSDAY_STEP_IDS = new Set([
    'attendance-classdays',
    'attendance-classday-button',
    'attendance-session-summary',
    'attendance-table',
    'attendance-status-select',
    'attendance-observation-button',
    'attendance-exports',
])

async function ensureGroupAndClassDay(context) {
    const groups = unref(context.groups) || []
    const formData = unref(context.formData)
    const formRef = unref(context.formRef)

    if (!groups.length || !formData) {
        return false
    }

    const tutorialGroupId = formData.training_group_id || groups[0].value
    const selectedMonth = Number(formData.month || new Date().getMonth() + 1)
    const monthCandidates = [
        selectedMonth,
        ...(context.optionsMonths || [])
            .map((month) => Number(month.value))
            .filter((month) => month !== selectedMonth),
    ]

    let foundClassDays = false

    for (const month of monthCandidates) {
        formData.training_group_id = tutorialGroupId
        formData.month = month

        formRef?.setFieldValue?.('training_group_id', tutorialGroupId)
        formRef?.setFieldValue?.('month', month)

        await nextTick()
        await context.actions.handleSearchClassdays({
            training_group_id: tutorialGroupId,
            month,
        })

        if ((unref(context.classDays) || []).length) {
            foundClassDays = true
            break
        }
    }

    if (!foundClassDays) {
        return false
    }

    const classDays = unref(context.classDays) || []
    const classDaySelected = unref(context.classDaySelected)

    if (!classDaySelected || !classDays.some((classDay) => classDay.id === classDaySelected?.id)) {
        await context.actions.clickClassDay(classDays[0])
        await nextTick()
    }

    return true
}

async function ensureObservationModal(context) {
    const hasSession = await ensureGroupAndClassDay(context)
    const attendancesGroup = unref(context.attendancesGroup) || []

    if (!hasSession || !attendancesGroup.length) {
        return false
    }

    await context.actions.openObservationModal(attendancesGroup[0])
    await nextTick()

    return true
}

export const attendancesTutorial = {
    getSteps(context) {
        const steps = [
            {
                id: 'attendance-search-panel',
                selector: '[data-tour="attendance-search-panel"]',
                title: 'Empieza por la busqueda',
                text: 'Desde este panel consultas el grupo y el mes que quieres trabajar antes de registrar asistencias.',
                tips: [
                    'El tutorial intentara cargar un grupo automaticamente si hay datos disponibles.',
                ],
            },
            {
                id: 'attendance-group-filter',
                selector: '[data-tour="attendance-group-filter"]',
                title: 'Selecciona el grupo',
                text: 'Primero eliges el grupo de entrenamiento para traer sus clases programadas.',
                tips: [
                    'No se incluyen grupos provisionales.',
                ],
            },
            {
                id: 'attendance-month-filter',
                selector: '[data-tour="attendance-month-filter"]',
                title: 'Indica el mes',
                text: 'Luego defines el mes en el que vas a consultar las jornadas de entrenamiento.',
                tips: [
                    'El tutorial probara primero el mes actual y luego otros meses si hace falta.',
                ],
            },
            {
                id: 'attendance-search-button',
                selector: '[data-tour="attendance-search-button"]',
                title: 'Consulta los entrenamientos',
                text: 'Con este boton cargas los dias de entrenamiento disponibles para el grupo y el mes elegidos.',
            },
            {
                id: 'attendance-classdays',
                selector: '[data-tour="attendance-classdays"]',
                title: 'Revisa los dias disponibles',
                text: 'Despues de consultar, aqui aparecen los entrenamientos programados para ese grupo.',
                tips: [
                    'Cada boton corresponde a una clase distinta.',
                ],
            },
            {
                id: 'attendance-classday-button',
                selector: '[data-tour="attendance-classday-button"]',
                title: 'Selecciona un entrenamiento',
                text: 'Haz clic en uno de los dias para cargar la lista de deportistas y habilitar la toma de asistencia.',
            },
            {
                id: 'attendance-session-summary',
                selector: '[data-tour="attendance-session-summary"]',
                title: 'Confirma la clase activa',
                text: 'En este encabezado validas que grupo y que entrenamiento estas registrando.',
            },
            {
                id: 'attendance-table',
                selector: '[data-tour="attendance-table"]',
                title: 'Registra la asistencia',
                text: 'Aqui veras todos los deportistas del grupo para diligenciar la asistencia y sus observaciones.',
            },
            {
                id: 'attendance-status-select',
                selector: '[data-tour="attendance-status-select"]',
                title: 'Marca el estado del deportista',
                text: 'En esta lista seleccionas si el deportista asistio, falto, presento excusa u otro estado.',
                tips: [
                    'El cambio se guarda inmediatamente al seleccionar una opcion.',
                ],
            },
            {
                id: 'attendance-observation-button',
                selector: '[data-tour="attendance-observation-button"]',
                title: 'Abre la observacion',
                text: 'Desde este boton puedes dejar comentarios especificos del deportista para ese entrenamiento.',
            },
            {
                id: 'attendance-observation-field',
                selector: '[data-tour="attendance-observation-field"]',
                title: 'Escribe y guarda la observacion',
                text: 'En este modal registras el detalle de la observacion y luego la guardas.',
                tips: [
                    'La observacion queda asociada a la fecha del entrenamiento activo.',
                ],
            },
        ]

        if (unref(context.exportPdf) || unref(context.exportExcel)) {
            steps.splice(6, 0, {
                id: 'attendance-exports',
                selector: '[data-tour="attendance-exports"]',
                title: 'Exporta la asistencia',
                text: 'Si lo necesitas, puedes generar el reporte de la clase en PDF o Excel.',
            })
        }

        return steps
    },

    async onBeforeStepChange({ step, context }) {
        if (!step) {
            return
        }

        if (step.id !== 'attendance-observation-field' && unref(context.takeAttendance)) {
            context.actions.closeObservationModal()
            await nextTick()
        }

        if (CLASSDAY_STEP_IDS.has(step.id)) {
            await ensureGroupAndClassDay(context)
        }

        if (step.id === 'attendance-observation-field') {
            await ensureObservationModal(context)
        }
    },

    async onAfterClose({ context }) {
        if (unref(context.takeAttendance)) {
            context.actions.closeObservationModal()
            await nextTick()
        }
    },
}
