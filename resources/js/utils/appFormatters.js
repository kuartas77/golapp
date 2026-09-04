import dayjs from '@/utils/dayjs'

const DEFAULT_DATE_FORMAT = 'DD/MM/YYYY'
const DEFAULT_FALLBACK = '—'

const STATUS_CATALOGS = {
    default: {
        active: { label: 'Activo', variant: 'success' },
        inactive: { label: 'Inactivo', variant: 'secondary' },
        enabled: { label: 'Habilitado', variant: 'success' },
        disabled: { label: 'Deshabilitado', variant: 'secondary' },
        pending: { label: 'Pendiente', variant: 'warning' },
    },
    invoice: {
        paid: { label: 'Pagada', variant: 'success' },
        partial: { label: 'Parcial', variant: 'warning' },
        pending: { label: 'Pendiente', variant: 'danger' },
        cancelled: { label: 'Anulada', variant: 'secondary' },
        canceled: { label: 'Anulada', variant: 'secondary' },
    },
    receipt: {
        paid: { label: 'Pagado', variant: 'success' },
        partial: { label: 'Parcial', variant: 'warning' },
        pending: { label: 'Pendiente', variant: 'danger' },
        cancelled: { label: 'Anulado', variant: 'secondary' },
        canceled: { label: 'Anulado', variant: 'secondary' },
    },
    'invoice-item': {
        true: { label: 'Pagado', variant: 'success' },
        1: { label: 'Pagado', variant: 'success' },
        paid: { label: 'Pagado', variant: 'success' },
        false: { label: 'Pendiente', variant: 'warning' },
        0: { label: 'Pendiente', variant: 'warning' },
        pending: { label: 'Pendiente', variant: 'warning' },
    },
    'custom-charge': {
        pending: { label: 'Pendiente', variant: 'warning' },
        due: { label: 'Debe', variant: 'danger' },
        paid: { label: 'Pagado', variant: 'success' },
    },
    match: {
        scheduled: { label: 'Programado', variant: 'warning' },
        played: { label: 'Jugado', variant: 'success' },
    },
}

const moneyFormatters = new Map()

const formatterKey = (locale, currency, minimumFractionDigits, maximumFractionDigits) => (
    [locale, currency, minimumFractionDigits, maximumFractionDigits].join('|')
)

const getMoneyFormatter = ({
    locale,
    currency,
    minimumFractionDigits,
    maximumFractionDigits,
}) => {
    const key = formatterKey(locale, currency, minimumFractionDigits, maximumFractionDigits)

    if (!moneyFormatters.has(key)) {
        moneyFormatters.set(key, new Intl.NumberFormat(locale, {
            style: 'currency',
            currency,
            minimumFractionDigits,
            maximumFractionDigits,
        }))
    }

    return moneyFormatters.get(key)
}

export const escapeAppHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')

export const formatAppDate = (value, {
    format = DEFAULT_DATE_FORMAT,
    fallback = DEFAULT_FALLBACK,
} = {}) => {
    if (value === null || value === undefined || value === '') {
        return fallback
    }

    const parsedDate = dayjs(value)

    return parsedDate.isValid() ? parsedDate.format(format) : fallback
}

export const appDateTime = (value) => {
    if (value === null || value === undefined || value === '') {
        return undefined
    }

    const parsedDate = dayjs(value)

    if (!parsedDate.isValid()) {
        return undefined
    }

    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return value
    }

    return parsedDate.toISOString()
}

export const formatAppMoney = (value, {
    locale = 'es-CO',
    currency = 'COP',
    minimumFractionDigits = 0,
    maximumFractionDigits = 0,
    fallback = DEFAULT_FALLBACK,
} = {}) => {
    if (value === null || value === undefined || value === '') {
        return fallback
    }

    const amount = Number(value)

    if (!Number.isFinite(amount)) {
        return fallback
    }

    return getMoneyFormatter({
        locale,
        currency,
        minimumFractionDigits,
        maximumFractionDigits,
    }).format(amount)
}

export const resolveAppStatus = (value, {
    context = 'default',
    label,
    variant,
    fallbackLabel = 'Sin estado',
    fallbackVariant = 'secondary',
} = {}) => {
    const normalizedValue = value === null || value === undefined || value === ''
        ? ''
        : String(value).toLowerCase()
    const catalog = STATUS_CATALOGS[context] ?? STATUS_CATALOGS.default
    const resolved = catalog[normalizedValue] ?? STATUS_CATALOGS.default[normalizedValue]

    return {
        label: label ?? resolved?.label ?? (normalizedValue ? String(value) : fallbackLabel),
        variant: variant ?? resolved?.variant ?? fallbackVariant,
    }
}

export const renderAppStatus = (value, {
    type = 'display',
    ...options
} = {}) => {
    const status = resolveAppStatus(value, options)

    if (type !== 'display') {
        return status.label
    }

    return `<span class="badge badge-${escapeAppHtml(status.variant)}">${escapeAppHtml(status.label)}</span>`
}
