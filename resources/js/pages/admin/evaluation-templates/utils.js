export const defaultStatusOptions = [
    { value: 'draft', label: 'Borrador' },
    { value: 'active', label: 'Activa' },
    { value: 'inactive', label: 'Inactiva' },
]

export const defaultScoreTypeOptions = [
    { value: 'numeric', label: 'Numérico' },
    { value: 'scale', label: 'Escala' },
]

export const statusVariantMap = {
    draft: 'secondary',
    active: 'success',
    inactive: 'warning',
}

export function labelFromOptions(options = [], value, fallback = '—') {
    const match = options.find((option) => option.value === value)
    return match?.label || fallback
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

export function toQueryObject(filters = {}) {
    return Object.entries(filters).reduce((accumulator, [key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            accumulator[key] = value
        }

        return accumulator
    }, {})
}

export function createEmptyCriterion(sortOrder = 1) {
    return {
        dimension: '',
        name: '',
        description: '',
        score_type: 'numeric',
        min_score: 1,
        max_score: 5,
        weight: 1,
        sort_order: sortOrder,
        is_required: true,
    }
}

export function normalizeCriterionPayload(criterion, index) {
    const scoreType = criterion.score_type === 'scale' ? 'scale' : 'numeric'

    return {
        dimension: String(criterion.dimension || '').trim(),
        name: String(criterion.name || '').trim(),
        description: String(criterion.description || '').trim() || null,
        score_type: scoreType,
        min_score: scoreType === 'numeric' && criterion.min_score !== '' ? Number(criterion.min_score) : null,
        max_score: scoreType === 'numeric' && criterion.max_score !== '' ? Number(criterion.max_score) : null,
        weight: Number(criterion.weight ?? 1),
        sort_order: Number(criterion.sort_order || (index + 1)),
        is_required: Boolean(criterion.is_required),
    }
}
