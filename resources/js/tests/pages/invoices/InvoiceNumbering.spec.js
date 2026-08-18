import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock } = vi.hoisted(() => ({
    axiosMock: { get: vi.fn(), post: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn() },
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))

import InvoiceNumbering from '@/pages/invoices/InvoiceNumbering.vue'

describe('InvoiceNumbering', () => {
    beforeEach(() => {
        Object.values(axiosMock).forEach(mock => mock.mockReset())
        axiosMock.get.mockResolvedValue({
            data: {
                electronic_invoicing_enabled: false,
                can_toggle_electronic_invoicing: true,
                ranges: [{
                    id: 7,
                    resolution_number: '18764012345678',
                    prefix: 'FE',
                    range_start: 1,
                    range_end: 500,
                    next_number: 25,
                    valid_from: '2026-01-01',
                    valid_until: '2026-12-31',
                    is_active: true,
                    state: 'active',
                    remaining_numbers: 476,
                    has_technical_key: true,
                }],
            },
        })
    })

    it('shows the active range and lets super-admin toggle electronic invoicing', async () => {
        axiosMock.patch.mockResolvedValue({ data: { electronic_invoicing_enabled: true } })
        const wrapper = mount(InvoiceNumbering, {
            global: {
                stubs: {
                    panel: { template: '<section><slot name="header"/><slot name="body"/></section>' },
                    breadcrumb: true,
                    AppPageHeader: { template: '<header><h1>{{ title }}</h1><slot name="actions"/></header>', props: ['title'] },
                    AppButton: { template: '<button><slot/></button>' },
                    ContentState: true,
                },
            },
        })
        await flushPromises()

        expect(wrapper.text()).toContain('18764012345678')
        expect(wrapper.text()).toContain('FE25')
        expect(wrapper.get('#electronic-invoicing-mode').exists()).toBe(true)

        await wrapper.get('#electronic-invoicing-mode').setValue(true)
        await flushPromises()

        expect(axiosMock.patch).toHaveBeenCalledWith('/api/v2/admin/invoice-numbering/electronic-mode', { enabled: true })
    })
})
