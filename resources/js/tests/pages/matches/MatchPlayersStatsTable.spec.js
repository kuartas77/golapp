import { flushPromises, mount } from '@vue/test-utils'
import { Form } from 'vee-validate'
import { describe, expect, it, vi } from 'vitest'

import MatchPlayersStatsTable from '@/pages/matches/components/MatchPlayersStatsTable.vue'

const skillControl = {
    id: 71,
    inscription_id: 21,
    assistance: 1,
    titular: 1,
    played_approx: 60,
    position: 'Defensa (Central)',
    goals: 1,
    goal_assists: 0,
    goal_saves: 0,
    yellow_cards: 0,
    red_cards: 0,
    qualification: 4,
    observation: 'Buen desempeño',
    player: {
        id: 15,
        full_names: 'Deportista de Prueba',
        unique_code: 'E2E-015',
        photo_url: '/img/user.webp',
    },
}

const secondSkillControl = {
    ...skillControl,
    id: 72,
    inscription_id: 22,
    player: {
        ...skillControl.player,
        id: 16,
        full_names: 'Segunda Deportista',
        unique_code: 'E2E-016',
    },
}

function mountTable(onSubmit = vi.fn()) {
    return {
        onSubmit,
        wrapper: mount({
            components: { Form, MatchPlayersStatsTable },
            data: () => ({
                initialValues: { skill_controls: [skillControl, secondSkillControl] },
                players: [skillControl, secondSkillControl],
                positions: [{ value: 'Defensa (Central)', label: 'Defensa central' }],
            }),
            methods: { onSubmit },
            template: `
                <Form :initial-values="initialValues" @submit="onSubmit">
                    <MatchPlayersStatsTable
                        :skills-controls="players"
                        :position-options="positions"
                    />
                    <button type="submit">Guardar</button>
                </Form>
            `,
        }),
    }
}

describe('MatchPlayersStatsTable', () => {
    it('keeps every skill control registered in the parent form payload', async () => {
        const { wrapper, onSubmit } = mountTable()

        expect(wrapper.findAll('thead th')).toHaveLength(6)
        expect(wrapper.findAll('img.player-avatar')).toHaveLength(2)
        expect(wrapper.find('img.player-avatar--competition-creation').exists()).toBe(false)
        expect(wrapper.findAll('[name="skill_controls[0].goals"]')).toHaveLength(1)
        expect(wrapper.findAll('[name="skill_controls[0].observation"]')).toHaveLength(1)

        const playerFilter = wrapper.get('[aria-label="Filtrar jugadores"]')
        await playerFilter.trigger('click')
        await wrapper.findAll('[role="option"]')[2].trigger('click')
        expect(wrapper.findAll('.match-player-row')[0].attributes('style')).toContain('display: none')
        expect(wrapper.findAll('.match-player-row')[1].attributes('style') ?? '').not.toContain('display: none')
        expect(wrapper.findAll('[name="skill_controls[0].goals"]')).toHaveLength(1)
        await playerFilter.trigger('click')
        await wrapper.findAll('[role="option"]')[0].trigger('click')
        expect(wrapper.findAll('.match-player-row')[0].attributes('style') ?? '').not.toContain('display: none')

        const minuteOptions = wrapper.findAll('[name="skill_controls[0].played_approx"] option')
        expect(minuteOptions.at(0).attributes('value')).toBe('0')
        expect(minuteOptions.at(-1).attributes('value')).toBe('90')

        await wrapper.get('[name="skill_controls[0].goals"]').setValue('3')
        await wrapper.get('[name="skill_controls[0].observation"]').setValue('Observación actualizada')
        await wrapper.get('form').trigger('submit')
        await flushPromises()

        expect(onSubmit).toHaveBeenCalledOnce()
        expect(onSubmit.mock.calls[0][0].skill_controls[0]).toMatchObject({
            id: 71,
            inscription_id: 21,
            goals: '3',
            observation: 'Observación actualizada',
        })
    })

    it('renders the existing empty state without form fields', () => {
        const wrapper = mount(MatchPlayersStatsTable, {
            props: { skillsControls: [], positionOptions: [] },
        })

        expect(wrapper.text()).toContain('El grupo no cuenta con integrantes')
        expect(wrapper.findAll('select')).toHaveLength(0)
    })
})
