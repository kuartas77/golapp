import { mount } from '@vue/test-utils'
import { defineComponent, nextTick } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, settingsStore } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
    settingsStore: {
        groups: [],
        categories: [],
        inscription_years: [{ value: 2026, label: '2026' }],
        paymentTypeOptions: [],
        paymentTypeLabels: {},
    },
}))

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}))

vi.mock('@/store/settings-store', () => ({
    useSetting: () => settingsStore,
}))

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}))

import useMonthlyPayments from '@/composables/payments/monthly-payments'

const mountComposable = () => mount(defineComponent({
    setup() {
        return useMonthlyPayments()
    },
    template: '<div />',
}))

const paymentRow = (id, fullNames) => ({
    id,
    player: {
        full_names: fullNames,
    },
})

describe('monthly payment list', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        apiMock.post.mockReset()
    })

    it('filters the loaded backend result locally by player name', async () => {
        const wrapper = mountComposable()

        wrapper.vm.groupPayments = [
            paymentRow(1, 'María José Pérez'),
            paymentRow(2, 'Carlos Gómez'),
        ]
        wrapper.vm.playerSearchTerm = 'maria jose'
        await nextTick()

        expect(wrapper.vm.filteredGroupPayments).toHaveLength(1)
        expect(wrapper.vm.filteredGroupPayments[0].id).toBe(1)
        expect(wrapper.vm.visiblePlayerCount).toBe(1)
        expect(apiMock.get).not.toHaveBeenCalled()

        wrapper.unmount()
    })

    it('restores every loaded row when the player search is cleared', async () => {
        const wrapper = mountComposable()

        wrapper.vm.groupPayments = [
            paymentRow(1, 'María José Pérez'),
            paymentRow(2, 'Carlos Gómez'),
        ]
        wrapper.vm.playerSearchTerm = 'carlos'
        await nextTick()
        wrapper.vm.playerSearchTerm = ''
        await nextTick()

        expect(wrapper.vm.filteredGroupPayments).toHaveLength(2)
        expect(wrapper.vm.visiblePlayerCount).toBe(2)

        wrapper.unmount()
    })
})
