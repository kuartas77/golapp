import { defineComponent } from 'vue'
import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))
vi.mock('@/composables/use-meta', () => ({ usePageTitle: vi.fn() }))
vi.mock('vue-router', () => ({ useRoute: () => ({ query: {} }) }))

import useReceivedPaymentReport from '@/composables/reports/received-payment-report'

const Harness = defineComponent({
    setup() {
        return useReceivedPaymentReport()
    },
    template: '<div />',
})

describe('useReceivedPaymentReport', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        axiosMock.post.mockReset()
        axiosMock.get.mockResolvedValue({
            data: {
                years: [{ value: 2026, label: '2026' }],
                groups: [{ value: 7, label: 'Grupo 7' }],
                defaultYear: 2026,
            },
        })
        axiosMock.post.mockResolvedValue({
            data: { message: 'El informe será enviado al correo electrónico registrado.' },
        })
        vi.stubGlobal('showMessage', vi.fn())
        vi.spyOn(window, 'open').mockImplementation(() => null)
    })

    it('exports one group immediately and queues all groups by email', async () => {
        const wrapper = mount(Harness)
        await flushPromises()

        wrapper.vm.form.training_group_id = 7
        wrapper.vm.form.player_search = ' Ana Torres '
        await wrapper.vm.exportReport()

        expect(window.open).toHaveBeenCalledWith(
            expect.stringContaining('training_group_id=7'),
            '_blank',
            'noopener',
        )
        expect(window.open.mock.calls[0][0]).toContain('player_search=Ana+Torres')
        expect(axiosMock.post).not.toHaveBeenCalled()

        window.open.mockClear()
        wrapper.vm.form.training_group_id = null
        await wrapper.vm.exportReport()

        expect(window.open).not.toHaveBeenCalled()
        expect(axiosMock.post).toHaveBeenCalledWith('/api/v2/reports/received-payments', {
            year: 2026,
            player_search: 'Ana Torres',
            show_item_amounts: true,
            show_total_paid: true,
        })
        expect(showMessage).toHaveBeenCalledWith('El informe será enviado al correo electrónico registrado.')
        expect(wrapper.vm.isSubmitting).toBe(false)
        wrapper.unmount()
    })
})
