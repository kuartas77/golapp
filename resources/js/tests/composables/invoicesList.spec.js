import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock, routerPushMock } = vi.hoisted(() => ({
    axiosMock: { get: vi.fn() },
    routerPushMock: vi.fn(),
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))
vi.mock('@/composables/use-meta', () => ({ usePageTitle: vi.fn() }))
vi.mock('vue-router', () => ({ useRouter: () => ({ push: routerPushMock }) }))

import useInvoicesList from '@/composables/invoices/invoicesList'

const Harness = defineComponent({
    setup() {
        return useInvoicesList()
    },
    template: '<div />',
})

const settingsResponse = {
    data: {
        normal_training_groups: [{ id: 10, name: 'Sub 12' }],
    },
}

describe('useInvoicesList', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        routerPushMock.mockReset()
        axiosMock.get.mockResolvedValue(settingsResponse)
    })

    it('exposes a recoverable error instead of representing a failed request as an empty result', async () => {
        const wrapper = mount(Harness)
        const callback = vi.fn()
        axiosMock.get.mockRejectedValueOnce({
            response: { data: { message: 'El servicio de facturación no está disponible.' } },
        })

        await wrapper.vm.options.ajax({ draw: 1 }, callback)

        expect(callback).toHaveBeenCalledWith({ data: [], recordsTotal: 0, recordsFiltered: 0 })
        expect(wrapper.vm.globalError).toBe('El servicio de facturación no está disponible.')

        axiosMock.get.mockResolvedValueOnce({
            data: { data: [], recordsTotal: 0, recordsFiltered: 0 },
        })
        await wrapper.vm.options.ajax({ draw: 2 }, callback)

        expect(wrapper.vm.globalError).toBe('')
        wrapper.unmount()
    })

    it('renders Colombian dates and accessible names for every invoice action', () => {
        const wrapper = mount(Harness)
        const columns = wrapper.vm.options.columns
        const row = {
            id: 42,
            invoice_number: 'FAC-0042',
            status: 'pending',
            url_print: '/facturas/42/imprimir',
            numbering_type: 'internal',
        }

        expect(columns[6].render('2026-08-05T15:00:00Z')).toBe('05/08/2026')
        expect(columns[5].render('cancelled', 'display', row)).toContain('Anulado')
        expect(columns[3].render(125000)).toContain('125.000')

        const actions = columns[7].render(42, 'display', row)
        expect(actions).toContain('aria-label="Ver recibo de caja FAC-0042"')
        expect(actions).toContain('aria-label="Imprimir recibo de caja FAC-0042"')
        expect(actions).toContain('aria-label="Revisar anulación de recibo de caja FAC-0042"')
        expect(actions).toContain('rel="noopener noreferrer"')
        wrapper.unmount()
    })

    it('keeps electronic invoice terminology per row', () => {
        const wrapper = mount(Harness)
        const actions = wrapper.vm.options.columns[7].render(43, 'display', {
            id: 43,
            invoice_number: 'FE43',
            numbering_type: 'electronic',
            status: 'paid',
            url_print: '/facturas/43/imprimir',
        })

        expect(actions).toContain('aria-label="Ver factura FE43"')
        expect(actions).toContain('aria-label="Imprimir factura FE43"')
        expect(wrapper.vm.options.columns[5].render('paid', 'display', { numbering_type: 'electronic' })).toContain('Pagada')
        wrapper.unmount()
    })
})
