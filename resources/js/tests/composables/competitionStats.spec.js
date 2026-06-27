import { describe, expect, it } from 'vitest'

import { compactQuery, formatDecimal, formatNumber } from '@/composables/competition/competitionStats'

describe('competition statistics helpers', () => {
    it('keeps only active URL filters', () => {
        expect(compactQuery({
            year: 2026,
            tournament_id: '',
            category: null,
        })).toEqual({ year: 2026 })
    })

    it('formats dashboard metrics for es-CO', () => {
        expect(formatNumber(1234)).toBe('1.234')
        expect(formatDecimal(66.666)).toBe('66,67')
    })
})
