export const METHODOLOGY_TYPES = {
    planning: 'planning',
    characterizationSheet: 'characterization_sheet',
    monthlyReport: 'monthly_report',
    categoryMonthlyReport: 'category_monthly_report',
}

export const methodologyTabs = [
    {
        type: METHODOLOGY_TYPES.planning,
        label: 'Planificación',
        title: 'Formato de planificación',
    },
    {
        type: METHODOLOGY_TYPES.characterizationSheet,
        label: 'Ficha técnica',
        title: 'Ficha técnica de caracterización',
    },
    {
        type: METHODOLOGY_TYPES.monthlyReport,
        label: 'Informe mensual',
        title: 'Informe mensual',
    },
    {
        type: METHODOLOGY_TYPES.categoryMonthlyReport,
        label: 'Informe mensual categoría',
        title: 'Informe mensual categoría',
    },
]

export const planningDiagramKeys = [
    'initial_phase',
    'central_phase_one',
    'central_phase_two',
    'central_phase_three',
]

export const methodologyFieldGroups = {
    [METHODOLOGY_TYPES.planning]: [
        {
            title: 'Encabezado',
            fields: [
                { key: 'category', label: 'Categoría' },
                { key: 'date', label: 'Fecha' },
                { key: 'coach', label: 'Entrenador' },
                { key: 'session', label: 'Sesión' },
                { key: 'objective', label: 'Objetivo', type: 'textarea', wide: true },
            ],
        },
        {
            title: 'Estructuras preferentes',
            fields: [
                { key: 'coordinative', label: 'Coordinativa', type: 'textarea' },
                { key: 'cognitive', label: 'Cognitiva', type: 'textarea' },
                { key: 'conditional', label: 'Condicional', type: 'textarea' },
                { key: 'emotional_volitional', label: 'Emotivo-volitiva', type: 'textarea' },
            ],
        },
        {
            title: 'Fases',
            fields: [
                { key: 'initial_phase_time', label: 'Fase inicial - Tiempo' },
                { key: 'initial_phase_dosage', label: 'Fase inicial - Dosificación', type: 'textarea' },
                { key: 'initial_phase_description', label: 'Fase inicial - Descripción', type: 'textarea' },
                { key: 'central_phase_one_time', label: 'Fase central 1 - Tiempo' },
                { key: 'central_phase_one_dosage', label: 'Fase central 1 - Dosificación', type: 'textarea' },
                { key: 'central_phase_one_description', label: 'Fase central 1 - Descripción', type: 'textarea' },
                { key: 'central_phase_two_time', label: 'Fase central 2 - Tiempo' },
                { key: 'central_phase_two_dosage', label: 'Fase central 2 - Dosificación', type: 'textarea' },
                { key: 'central_phase_two_description', label: 'Fase central 2 - Descripción', type: 'textarea' },
                { key: 'central_phase_three_time', label: 'Fase central 3 - Tiempo' },
                { key: 'central_phase_three_dosage', label: 'Fase central 3 - Dosificación', type: 'textarea' },
                { key: 'central_phase_three_description', label: 'Fase central 3 - Descripción', type: 'textarea' },
                { key: 'final_phase_time', label: 'Fase final - Tiempo' },
                { key: 'final_phase_description', label: 'Fase final - Descripción', type: 'textarea' },
                { key: 'final_phase_dosage', label: 'Fase final - Dosificación', type: 'textarea' },
            ],
        },
        {
            title: 'Cierre',
            fields: [
                { key: 'material', label: 'Material', type: 'textarea' },
                { key: 'observations', label: 'Observaciones', type: 'textarea' },
            ],
        },
    ],
    [METHODOLOGY_TYPES.characterizationSheet]: [
        {
            title: 'Ficha técnica de caracterización',
            fields: [
                { key: 'category', label: 'Categoría' },
                { key: 'year_semester', label: 'Año-semestre' },
                { key: 'age_group', label: 'Grupo etario' },
                { key: 'competitions', label: 'Competencias 2026' },
                { key: 'sport_objectives', label: 'Objetivos deportivos 2026 (entrenador)', type: 'textarea' },
                { key: 'formative_objectives', label: 'Objetivos formativos de la categoría año 2026', type: 'textarea' },
                { key: 'constitutive_values', label: 'Valores constitutivos de la categoría', type: 'textarea' },
                { key: 'tactical_schemes', label: 'Esquemas tácticos habituales', type: 'textarea' },
                { key: 'game_model', label: 'Modelo de juego', type: 'textarea' },
                { key: 'offensive_defensive_principles', label: 'Principios ofensivos y defensivos trabajados', type: 'textarea' },
                { key: 'priority_technical_elements', label: 'Elementos técnicos prioritarios', type: 'textarea' },
                { key: 'internal_rules', label: 'Reglamento interno de la categoría', type: 'textarea' },
                { key: 'medical_prescription_player_1_name', label: 'Prescripción médica 1 - Nombre del jugador' },
                { key: 'medical_prescription_player_1_condition', label: 'Prescripción médica 1 - Condición' },
                { key: 'medical_prescription_player_2_name', label: 'Prescripción médica 2 - Nombre del jugador' },
                { key: 'medical_prescription_player_2_condition', label: 'Prescripción médica 2 - Condición' },
                { key: 'medical_prescription_player_3_name', label: 'Prescripción médica 3 - Nombre del jugador' },
                { key: 'medical_prescription_player_3_condition', label: 'Prescripción médica 3 - Condición' },
                { key: 'projection_player_1_name', label: 'Jugador con proyección 1 - Nombre del jugador' },
                { key: 'projection_player_1_qualities', label: 'Jugador con proyección 1 - Cualidades' },
                { key: 'projection_player_2_name', label: 'Jugador con proyección 2 - Nombre del jugador' },
                { key: 'projection_player_2_qualities', label: 'Jugador con proyección 2 - Cualidades' },
                { key: 'projection_player_3_name', label: 'Jugador con proyección 3 - Nombre del jugador' },
                { key: 'projection_player_3_qualities', label: 'Jugador con proyección 3 - Cualidades' },
            ],
        },
    ],
    [METHODOLOGY_TYPES.monthlyReport]: [
        {
            title: 'Informe mensual',
            fields: [
                { key: 'coach', label: 'Entrenador' },
                { key: 'category', label: 'Categoría' },
                { key: 'report_month', label: 'Mes correspondiente al informe' },
                { key: 'coach_obligation_1_activity', label: 'Obligación 1 - Actividad realizada', type: 'textarea' },
                { key: 'coach_obligation_1_support', label: 'Obligación 1 - Soporte', type: 'textarea' },
                { key: 'coach_obligation_2_activity', label: 'Obligación 2 - Actividad realizada', type: 'textarea' },
                { key: 'coach_obligation_2_support', label: 'Obligación 2 - Soporte', type: 'textarea' },
                { key: 'coach_obligation_3_activity', label: 'Obligación 3 - Actividad realizada', type: 'textarea' },
                { key: 'coach_obligation_3_support', label: 'Obligación 3 - Soporte', type: 'textarea' },
                { key: 'coach_obligation_4_activity', label: 'Obligación 4 - Actividad realizada', type: 'textarea' },
                { key: 'coach_obligation_4_support', label: 'Obligación 4 - Soporte', type: 'textarea' },
                { key: 'coach_obligation_5_activity', label: 'Obligación 5 - Actividad realizada', type: 'textarea' },
                { key: 'coach_obligation_5_support', label: 'Obligación 5 - Soporte', type: 'textarea' },
                { key: 'coach_obligation_6_activity', label: 'Obligación 6 - Actividad realizada', type: 'textarea' },
                { key: 'coach_obligation_6_support', label: 'Obligación 6 - Soporte', type: 'textarea' },
                { key: 'coach_obligation_7_activity', label: 'Obligación 7 - Actividad realizada', type: 'textarea' },
                { key: 'coach_obligation_7_support', label: 'Obligación 7 - Soporte', type: 'textarea' },
            ],
        },
    ],
    [METHODOLOGY_TYPES.categoryMonthlyReport]: [
        {
            title: 'Informe mensual categoría',
            fields: [
                { key: 'coach', label: 'Entrenador' },
                { key: 'category', label: 'Categoría' },
                { key: 'report_month', label: 'Mes correspondiente al informe' },
                { key: 'monthly_objectives_description', label: 'Objetivos planteados en el mes en curso', type: 'textarea' },
                { key: 'monthly_achievements_description', label: 'Logros obtenidos en el mes en curso', type: 'textarea' },
                { key: 'monthly_difficulties_description', label: 'Dificultades presentadas en el mes en curso', type: 'textarea' },
                { key: 'sport_values_description', label: 'Valores deportivos abordados', type: 'textarea' },
                { key: 'specific_player_news_description', label: 'Situaciones o novedades específicas con jugadores', type: 'textarea' },
                { key: 'player_follow_up_description', label: 'Seguimiento y/o control que se llevó o se está llevando a cabo con el jugador', type: 'textarea' },
            ],
        },
    ],
}

export function getTabByType(type) {
    return methodologyTabs.find((tab) => tab.type === type) ?? methodologyTabs[0]
}

export function createBlankFields(type) {
    return Object.fromEntries(
        (methodologyFieldGroups[type] ?? [])
            .flatMap((group) => group.fields)
            .map((field) => [field.key, ''])
    )
}

export function createBlankDiagrams() {
    return Object.fromEntries(planningDiagramKeys.map((key) => [key, []]))
}
