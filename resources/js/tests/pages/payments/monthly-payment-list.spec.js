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
        normal_training_groups: [],
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

vi.mock('@/store/auth-user', () => ({
    useAuthUser: () => ({
        hasSchoolPermission: vi.fn(() => true),
    }),
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

const paymentRowWithStatus = (status, inscriptionDeleted = false) => ({
    ...paymentRow(1, 'María José Pérez'),
    january: status,
    inscription_deleted: inscriptionDeleted,
})

describe('monthly payment list', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        apiMock.post.mockReset()
        apiMock.get.mockImplementation((url) => {
            if (url === '/api/v2/payments/status-catalog') {
                return Promise.resolve({ data: { statuses: [], groups: { paid: [1, 9, 10, 11, 12, 15], debt: [2], player_credit: [15] }, months: [] } })
            }

            return Promise.resolve({ data: {} })
        })
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
        expect(apiMock.get).not.toHaveBeenCalledWith('/api/v2/payments', expect.anything())

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

    it('allows editing no aplica payments when the inscription is active', () => {
        const wrapper = mountComposable()

        expect(wrapper.vm.canEditPaymentRow(paymentRowWithStatus(14), 'january')).toBe(true)
        expect(wrapper.vm.canEditPaymentRow(paymentRowWithStatus(14, true), 'january')).toBe(false)

        wrapper.unmount()
    })

    it('allows a year-only search for historical years', async () => {
        const wrapper = mountComposable()

        await expect(wrapper.vm.schema.validate({
            year: 2025,
            training_group_id: null,
            category: null,
        })).resolves.toBeTruthy()

        apiMock.get.mockImplementation((url) => {
            if (url === '/api/v2/payments/status-catalog') {
                return Promise.resolve({ data: { statuses: [], groups: { paid: [1, 9, 10, 11, 12, 15], debt: [2], player_credit: [15] }, months: [] } })
            }

            return Promise.resolve({
                data: {
                    rows: [
                        { ...paymentRow(1, 'Jugador Uno'), category: 'SUB-8' },
                        { ...paymentRow(2, 'Jugador Dos'), category: 'SUB-10' },
                    ],
                    count: 2,
                    url_export_excel: null,
                    url_export_pdf: null,
                    filter_options: {
                        categories: [
                            { value: 'SUB-8', label: 'SUB-8' },
                            { value: 'SUB-10', label: 'SUB-10' },
                        ],
                        groups: [
                            { value: 7, label: 'Histórico A' },
                            { value: 8, label: 'Histórico B' },
                        ],
                    },
                },
            })
        })

        await wrapper.vm.handleSearch({
            year: 2025,
            training_group_id: null,
            category: null,
        }, { setErrors: vi.fn() })

        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/payments', {
            params: {
                category: null,
                year: 2025,
                training_group_id: null,
                month: null,
                status: null,
                player_name: null,
                unique_code: null,
                dataRaw: true,
            },
        })
        expect(wrapper.vm.categories).toEqual([
            { value: 'SUB-8', label: 'SUB-8' },
            { value: 'SUB-10', label: 'SUB-10' },
        ])
        expect(wrapper.vm.groups).toEqual([
            { value: 7, label: 'Histórico A' },
            { value: 8, label: 'Histórico B' },
        ])

        wrapper.unmount()
    })

    it('requires group or category for the current year and allows either filter', async () => {
        const wrapper = mountComposable()

        await expect(wrapper.vm.schema.validate({
            year: 2026,
            training_group_id: null,
            category: null,
        })).rejects.toThrow('Para el año actual selecciona un grupo o una categoría.')

        await expect(wrapper.vm.schema.validate({
            year: 2026,
            training_group_id: '5',
            category: null,
        })).resolves.toBeTruthy()

        await expect(wrapper.vm.schema.validate({
            year: 2026,
            training_group_id: null,
            category: 'Sub 10',
        })).resolves.toBeTruthy()

        wrapper.unmount()
    })
})
