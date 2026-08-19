import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'

import AttendancePlayerCard from '@/pages/attendances/components/AttendancePlayerCard.vue'

const attendanceTypes = {
    1: 'Asistencia',
    2: 'Falta',
    3: 'Excusa',
}

function playerRow(overrides = {}) {
    return {
        id: 12,
        inscription_deleted: false,
        period_locked: false,
        inscription: {
            player: {
                full_names: 'Laura Gómez',
                unique_code: 'GOL-0152',
                category: 'Sub 15',
                photo_url: '/img/laura.webp',
            },
        },
        ...overrides,
    }
}

describe('AttendancePlayerCard', () => {
    it('renders the selected attendance and emits the existing actions', async () => {
        const wrapper = mount(AttendancePlayerCard, {
            props: {
                row: playerRow(),
                attendanceTypes,
                attendanceValue: 2,
            },
        })

        expect(wrapper.text()).toContain('Laura Gómez')
        expect(wrapper.text()).toContain('GOL-0152')
        expect(wrapper.attributes('data-status')).toBe('2')
        expect(wrapper.get('[data-tour="attendance-status-select"]').element.value).toBe('2')

        await wrapper.get('[data-tour="attendance-status-select"]').setValue('1')
        await wrapper.get('[data-tour="attendance-observation-button"]').trigger('click')

        expect(wrapper.emitted('attendance-change')).toEqual([['1']])
        expect(wrapper.emitted('open-observation')).toHaveLength(1)
    })

    it('disables attendance and observation controls for read-only rows', () => {
        const wrapper = mount(AttendancePlayerCard, {
            props: {
                row: playerRow({ period_locked: true }),
                attendanceTypes,
                attendanceValue: '',
                readOnly: true,
            },
        })

        expect(wrapper.text()).toContain('Periodo cerrado')
        expect(wrapper.get('[data-tour="attendance-status-select"]').attributes('disabled')).toBeDefined()
        expect(wrapper.get('[data-tour="attendance-observation-button"]').attributes('disabled')).toBeDefined()
    })
})
