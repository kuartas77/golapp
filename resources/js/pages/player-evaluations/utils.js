export const defaultStatusOptions = [
    { value: 'draft', label: 'Borrador' },
    { value: 'completed', label: 'Completada' },
    { value: 'closed', label: 'Cerrada' },
]

export const defaultEvaluationTypeOptions = [
    { value: 'initial', label: 'Inicial' },
    { value: 'periodic', label: 'Periódica' },
    { value: 'final', label: 'Final' },
    { value: 'special', label: 'Especial' },
]

export const statusVariantMap = {
    draft: 'secondary',
    completed: 'success',
    closed: 'dark',
}

export const trendVariantMap = {
    up: 'success',
    down: 'danger',
    equal: 'primary',
    neutral: 'secondary',
}

export const trendLabelMap = {
    up: 'Mejora',
    down: 'Descenso',
    equal: 'Sin cambio',
    neutral: 'Sin datos',
}

export function labelFromOptions(options = [], value, fallback = '—') {
    const match = options.find((option) => option.value === value)
    return match?.label || fallback
}

export function formatDate(value) {
    if (!value) return '—'

    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '—'

    return new Intl.DateTimeFormat('es-CO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(date)
}

export function formatDateTime(value) {
    if (!value) return '—'

    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '—'

    return new Intl.DateTimeFormat('es-CO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

export function formatScore(value, digits = 2) {
    if (value === null || value === undefined || value === '') {
        return '—'
    }

    const number = Number(value)

    if (Number.isNaN(number)) {
        return '—'
    }

    return number.toFixed(digits)
}

export function numberOrNull(value) {
    if (value === null || value === undefined || value === '') {
        return null
    }

    const parsed = Number(value)
    return Number.isNaN(parsed) ? null : parsed
}

export function datetimeLocalValue(value) {
    if (!value) return ''

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return ''
    }

    const offset = date.getTimezoneOffset()
    const adjusted = new Date(date.getTime() - (offset * 60 * 1000))

    return adjusted.toISOString().slice(0, 16)
}

export function toQueryObject(filters = {}) {
    return Object.entries(filters).reduce((accumulator, [key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            accumulator[key] = value
        }

        return accumulator
    }, {})
}

export function buildPdfUrl(basePath, query = {}) {
    const params = new URLSearchParams(toQueryObject(query)).toString()
    return params ? `${basePath}?${params}` : basePath
}

export function groupScoresByDimension(scores = []) {
    return scores.reduce((accumulator, item) => {
        const dimension = item?.criterion?.dimension || 'Sin dimensión'

        if (!accumulator[dimension]) {
            accumulator[dimension] = []
        }

        accumulator[dimension].push(item)
        accumulator[dimension].sort((left, right) => {
            const leftOrder = left?.criterion?.sort_order ?? 9999
            const rightOrder = right?.criterion?.sort_order ?? 9999
            return leftOrder - rightOrder
        })

        return accumulator
    }, {})
}

export function getValidationMessage(error, fallback = 'Ocurrió un error inesperado.') {
    const messages = error?.response?.data?.errors

    if (!messages || typeof messages !== 'object') {
        return error?.response?.data?.message || error?.message || fallback
    }

    return Object.values(messages)
        .flat()
        .filter(Boolean)
        .join(' ')
}

export function criterionHasValue(score) {
    return (
        score?.score !== null &&
        score?.score !== undefined &&
        score?.score !== ''
    ) || (
        score?.scale_value !== null &&
        score?.scale_value !== undefined &&
        score?.scale_value !== ''
    )
}

export function playerNameFromEvaluation(evaluation) {
    return evaluation?.inscription?.player?.name || 'Jugador sin nombre'
}

export function groupNameFromEvaluation(evaluation) {
    return evaluation?.inscription?.training_group?.name || 'Sin grupo'
}
