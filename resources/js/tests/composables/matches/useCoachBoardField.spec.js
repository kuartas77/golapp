import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

import {
    COACHBOARD_POSITION_ROLES,
    normalizeCoachBoardPositionRole
} from '@/composables/matches/useCoachBoardField'

function getConfigKeyPositions() {
    const config = readFileSync(resolve(process.cwd(), 'config/variables.php'), 'utf8')
    const keyPositionsBlock = config.match(/'KEY_POSITIONS'\s*=>\s*\[(?<body>[\s\S]*?)\n\s*\],/)?.groups?.body ?? ''
    const hiddenPositionsBlock = config.match(/'KEY_POSITIONS_HIDDEN_FROM_SELECT'\s*=>\s*\[(?<body>[\s\S]*?)\n\s*\],/)?.groups?.body ?? ''
    const hiddenPositions = new Set(
        [...hiddenPositionsBlock.matchAll(/'([^']+)'\s*=>\s*'([^']+)'/g)].map((match) => match[2])
    )

    return [...keyPositionsBlock.matchAll(/'([^']+)'\s*=>\s*'([^']+)'/g)]
        .map((match) => match[2])
        .filter((position) => !hiddenPositions.has(position))
}

describe('normalizeCoachBoardPositionRole', () => {
    it('keeps coachboard positions aligned with KEY_POSITIONS formatting', () => {
        expect(normalizeCoachBoardPositionRole('Defensa(Izquierdo)')).toBe('Defensa (Izquierdo)')
        expect(normalizeCoachBoardPositionRole('Defensa (Derecho) (Izquierdo)')).toBe('Defensa (Derecho)(Izquierdo)')
        expect(normalizeCoachBoardPositionRole('Volante(Segunda línea)')).toBe('Volante (Segunda línea)')
        expect(normalizeCoachBoardPositionRole('Delantero(Central)')).toBe('Delantero (Central)')
    })

    it('keeps the coachboard role catalog aligned with visible config KEY_POSITIONS', () => {
        expect(Object.values(COACHBOARD_POSITION_ROLES)).toEqual(getConfigKeyPositions())
    })
})
