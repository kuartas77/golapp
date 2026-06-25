import { describe, expect, it } from 'vitest'

import {
    buildSkillControlLookup,
    findMatchingSkillControl,
    getSkillControlMatchKeys
} from '@/pages/matches/utils/skillControls'

describe('match skill control helpers', () => {
    it('matches coachboard lineup items by inscription id when player payloads differ', () => {
        const lineupItem = {
            inscription_id: 25,
            titular: 1,
            position: 'Defensa (Izquierdo)',
            player: { id: 99, full_names: 'Jugador Titular' }
        }

        const skillControl = {
            inscription_id: '25',
            player: { id: 12, full_names: 'Jugador Titular' }
        }

        const lookup = buildSkillControlLookup([lineupItem])

        expect(findMatchingSkillControl(lookup, skillControl)).toBe(lineupItem)
    })

    it('builds stable lookup keys from player, inscription, and code shapes', () => {
        expect(getSkillControlMatchKeys({
            inscription_id: 10,
            player: { id: 20, unique_code: 'ABC' },
            inscription: {
                id: 30,
                player: { id: 40, unique_code: 'XYZ' }
            }
        })).toEqual([
            'inscription:10',
            'inscription:30',
            'player:20',
            'player:40',
            'code:ABC',
            'code:XYZ'
        ])
    })
})
