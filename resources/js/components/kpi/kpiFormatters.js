export const currencyFormatter = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
})

export const numberFormatter = new Intl.NumberFormat('es-CO')

export const compactNumberFormatter = new Intl.NumberFormat('es-CO', {
    notation: 'compact',
    compactDisplay: 'short',
    maximumFractionDigits: 1,
})

export const formatMetricValue = (value, format = 'number') => {
    const normalizedValue = Number(value ?? 0)

    if (format === 'currency') {
        return currencyFormatter.format(normalizedValue)
    }

    if (format === 'percentage') {
        return `${normalizedValue.toFixed(2)}%`
    }

    return numberFormatter.format(normalizedValue)
}

export const formatCompactCurrency = (value) => `$${compactNumberFormatter.format(Number(value || 0))}`
