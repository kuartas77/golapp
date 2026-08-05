import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock } = vi.hoisted(() => ({
    axiosMock: { get: vi.fn() },
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))
vi.mock('@/composables/use-meta', () => ({ usePageTitle: vi.fn() }))

import useInvoiceItemsList from '@/composables/invoices/invoiceItemsList'

const Harness = defineComponent({
    setup() {
        return useInvoiceItemsList()
    },
    template: '<div />',
})

describe('useInvoiceItemsList', () => {
    beforeEach(() => axiosMock.get.mockReset())

    it('distinguishes a server failure from a valid empty response and recovers', async () => {
        const wrapper = mount(Harness)
        const callback = vi.fn()
        axiosMock.get.mockRejectedValueOnce({
            response: { data: { message: 'No se pudieron consultar los conceptos.' } },
        })

        await wrapper.vm.options.ajax({ draw: 1 }, callback)

        expect(axiosMock.get).toHaveBeenLastCalledWith('/api/v2/invoices/items/invoices', { params: { draw: 1 } })
        expect(wrapper.vm.globalError).toBe('No se pudieron consultar los conceptos.')
        expect(callback).toHaveBeenLastCalledWith({
            draw: 1,
            data: [],
            recordsTotal: 0,
            recordsFiltered: 0,
        })

        axiosMock.get.mockResolvedValueOnce({
            data: { data: [], recordsTotal: 0, recordsFiltered: 0 },
        })
        await wrapper.vm.options.ajax({ draw: 2 }, callback)

        expect(wrapper.vm.globalError).toBe('')
        wrapper.unmount()
    })

    it('uses Colombian display dates', () => {
        const wrapper = mount(Harness)

        expect(wrapper.vm.options.columns[1].render('2026-08-05T10:00:00Z')).toBe('05/08/2026')
        expect(wrapper.vm.options.columns[9].render(true)).toContain('Pagado')
        expect(wrapper.vm.options.columns[8].render(90000)).toContain('90.000')
        wrapper.unmount()
    })
})
