import { defineComponent } from 'vue'
import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock, settingsMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
    },
    settingsMock: {
        groups: [{ id: 7, name: 'Sub 15', full_group: 'Sub 15' }],
        getSettings: vi.fn(),
    },
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))
vi.mock('@/composables/use-meta', () => ({ usePageTitle: vi.fn() }))
vi.mock('vue-router', () => ({ useRoute: () => ({ query: {} }) }))
vi.mock('@/store/settings-store', () => ({ useSetting: () => settingsMock }))

import useSchoolList from '@/composables/admin/school/schoolList'
import useInstructorActivityReport from '@/composables/reports/instructor-activity-report'
import useAttendancePaymentReport from '@/composables/reports/attendance-payment-report'
import useAssistReports from '@/composables/reports/assist-reports'

const emptyResult = {
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
}

const mountComposable = (composable) => {
    let state
    const wrapper = mount(defineComponent({
        setup() {
            state = composable()
            return () => null
        },
    }))

    return { state, wrapper }
}

describe('recoverable administration and report tables', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        settingsMock.getSettings.mockReset()
    })

    it('does not present a failed schools request as an empty result', async () => {
        axiosMock.get.mockRejectedValue({
            response: { data: { message: 'Escuelas temporalmente no disponibles.' } },
        })
        const { options, listError } = useSchoolList()
        const callback = vi.fn()

        await options.ajax({ draw: 3 }, callback)

        expect(listError.value).toBe('Escuelas temporalmente no disponibles.')
        expect(callback).toHaveBeenCalledWith(emptyResult)
    })

    it('keeps the instructor table error until a successful request clears it', async () => {
        axiosMock.get.mockResolvedValue({
            data: { years: [], months: [], instructors: [] },
        })
        const { state, wrapper } = mountComposable(useInstructorActivityReport)
        await flushPromises()

        axiosMock.get.mockRejectedValueOnce({
            response: { data: { message: 'No se pudo consultar la actividad.' } },
        })
        await state.options.ajax({ draw: 4 }, vi.fn())
        expect(state.tableError.value).toBe('No se pudo consultar la actividad.')

        axiosMock.get.mockResolvedValueOnce({ data: { ...emptyResult, draw: 5 } })
        await state.options.ajax({ draw: 5 }, vi.fn())
        expect(state.tableError.value).toBe('')
        wrapper.unmount()
    })

    it('tracks attendance-payment failures independently for each table', async () => {
        axiosMock.get.mockResolvedValue({
            data: { years: [], months: [], groups: [] },
        })
        const { state, wrapper } = mountComposable(useAttendancePaymentReport)
        await flushPromises()

        axiosMock.get
            .mockRejectedValueOnce({ response: { data: { message: 'Falló el resumen.' } } })
            .mockRejectedValueOnce({ response: { data: { message: 'Falló el detalle.' } } })

        await state.summaryOptions.ajax({ draw: 1 }, vi.fn())
        await state.playerOptions.ajax({ draw: 2 }, vi.fn())

        expect(state.summaryTableError.value).toBe('Falló el resumen.')
        expect(state.playerTableError.value).toBe('Falló el detalle.')
        wrapper.unmount()
    })

    it('tracks each attendance report failure without hiding the other sections', async () => {
        axiosMock.get.mockResolvedValue({ data: { years: [], months: [] } })
        const { state, wrapper } = mountComposable(useAssistReports)
        await flushPromises()

        axiosMock.get
            .mockRejectedValueOnce({ response: { data: { message: 'Falló jugadores.' } } })
            .mockRejectedValueOnce({ response: { data: { message: 'Falló grupos.' } } })
            .mockRejectedValueOnce({ response: { data: { message: 'Falló anual.' } } })

        await state.monthlyPlayerOptions.ajax({ draw: 1 }, vi.fn())
        await state.monthlyGroupOptions.ajax({ draw: 2 }, vi.fn())
        await state.annualConsolidatedOptions.ajax({ draw: 3 }, vi.fn())

        expect(state.monthlyPlayerError.value).toBe('Falló jugadores.')
        expect(state.monthlyGroupError.value).toBe('Falló grupos.')
        expect(state.annualConsolidatedError.value).toBe('Falló anual.')
        wrapper.unmount()
    })
})
