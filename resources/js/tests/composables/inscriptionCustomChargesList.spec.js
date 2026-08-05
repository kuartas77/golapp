import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock } = vi.hoisted(() => ({
    axiosMock: { get: vi.fn() },
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))

import useInscriptionCustomChargesList from '@/composables/invoices/inscriptionCustomChargesList'

const Harness = defineComponent({
    setup() {
        return useInscriptionCustomChargesList()
    },
    template: '<div />',
})

describe('useInscriptionCustomChargesList', () => {
    beforeEach(() => axiosMock.get.mockReset())

    it('exposes the API error separately and clears it after a successful retry', async () => {
        const wrapper = mount(Harness)
        const callback = vi.fn()
        axiosMock.get.mockRejectedValueOnce({
            response: { data: { message: 'No se pudieron consultar los cargos.' } },
        })

        await wrapper.vm.options.ajax({ draw: 8 }, callback)

        expect(wrapper.vm.globalError).toBe('No se pudieron consultar los cargos.')
        expect(callback).toHaveBeenLastCalledWith({
            draw: 8,
            data: [],
            recordsTotal: 0,
            recordsFiltered: 0,
        })

        axiosMock.get.mockResolvedValueOnce({
            data: { data: [], recordsTotal: 0, recordsFiltered: 0 },
        })
        await wrapper.vm.options.ajax({ draw: 9 }, callback)

        expect(wrapper.vm.globalError).toBe('')
        wrapper.unmount()
    })

    it('uses the product vocabulary for the athlete column', () => {
        const wrapper = mount(Harness)

        expect(wrapper.vm.options.columns[0].title).toBe('Deportista')
        wrapper.unmount()
    })
})
