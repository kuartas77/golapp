import { mount } from '@vue/test-utils'
import { defineComponent, nextTick } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, settingsStore, authStore } = vi.hoisted(() => ({
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
    authStore: {
        hasSchoolPermission: vi.fn(() => true),
    },
}))

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}))

vi.mock('@/store/settings-store', () => ({
    useSetting: () => settingsStore,
}))

vi.mock('@/store/auth-user', () => ({
    useAuthUser: () => authStore,
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
    unique_code: `PAY-${id}`,
    player: {
        full_names: fullNames,
        unique_code: `PLY-${id}`,
    },
})

const paymentRowWithStatus = (status, inscriptionDeleted = false) => ({
    ...paymentRow(1, 'María José Pérez'),
    january: status,
    history_fields: {},
    inscription_deleted: inscriptionDeleted,
})

const currentMonthField = () => ({
    0: 'january',
    1: 'february',
    2: 'march',
    3: 'april',
    4: 'may',
    5: 'june',
    6: 'july',
    7: 'august',
    8: 'september',
    9: 'october',
    10: 'november',
    11: 'december',
}[new Date().getMonth()])

const statusCatalogFixture = () => ({
    statuses: [
        { value: '2', label: 'Debe' },
        { value: '9', label: 'Pagó - Efectivo' },
    ],
    groups: { paid: [1, 9, 10, 11, 12, 15], debt: [2], player_credit: [15] },
    months: [],
})

describe('monthly payment list', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        apiMock.post.mockReset()
        authStore.hasSchoolPermission.mockReset()
        authStore.hasSchoolPermission.mockReturnValue(true)
        globalThis.Swal = {
            fire: vi.fn(() => Promise.resolve({ isConfirmed: true })),
        }
        globalThis.showMessage = vi.fn()
        apiMock.get.mockImplementation((url) => {
            if (url === '/api/v2/payments/status-catalog') {
                return Promise.resolve({ data: statusCatalogFixture() })
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

    it('filters the loaded backend result locally by player code', async () => {
        const wrapper = mountComposable()

        wrapper.vm.groupPayments = [
            paymentRow(1, 'María José Pérez'),
            paymentRow(2, 'Carlos Gómez'),
        ]
        wrapper.vm.playerSearchTerm = 'ply-2'
        await nextTick()

        expect(wrapper.vm.filteredGroupPayments).toHaveLength(1)
        expect(wrapper.vm.filteredGroupPayments[0].id).toBe(2)
        expect(apiMock.get).not.toHaveBeenCalledWith('/api/v2/payments', expect.anything())

        wrapper.unmount()
    })

    it('allows editing no aplica payments when the inscription is active', () => {
        const wrapper = mountComposable()

        expect(wrapper.vm.canEditPaymentRow(paymentRowWithStatus(14), 'january')).toBe(true)
        expect(wrapper.vm.canEditPaymentRow(paymentRowWithStatus(14, true), 'january')).toBe(false)

        wrapper.unmount()
    })

    it('hides payment history affordance for pending monthly cells', () => {
        const wrapper = mountComposable()

        expect(wrapper.vm.canShowPaymentHistory({
            ...paymentRowWithStatus(2),
            history_fields: {},
        }, 'january')).toBe(false)
        expect(wrapper.vm.canShowPaymentHistory({
            ...paymentRowWithStatus(0),
            history_fields: { january: 1 },
        }, 'january')).toBe(true)

        wrapper.unmount()
    })

    it('defaults the month filter to the current month', () => {
        const wrapper = mountComposable()

        expect(wrapper.vm.formData.month).toBe(currentMonthField())
        expect(wrapper.vm.selectedMonthField).toBe(currentMonthField())
        expect(wrapper.vm.monthOptions.some((option) => option.value === '')).toBe(false)

        wrapper.unmount()
    })

    it('exposes player credit help only when the school permission is enabled', () => {
        authStore.hasSchoolPermission.mockReturnValue(false)
        const wrapper = mountComposable()

        expect(wrapper.vm.canUsePlayerCredits).toBe(false)

        wrapper.unmount()
    })

    it('loads payment history for a selected row', async () => {
        const wrapper = mountComposable()
        apiMock.get.mockImplementation((url) => {
            if (url === '/api/v2/payments/status-catalog') {
                return Promise.resolve({ data: statusCatalogFixture() })
            }

            if (url === '/api/v2/payments/7/history') {
                return Promise.resolve({
                    data: {
                        data: [
                            { id: 1, field: 'january', month_label: 'Enero', source: 'manual' },
                        ],
                    },
                })
            }

            return Promise.resolve({ data: {} })
        })

        await wrapper.vm.openPaymentHistory(paymentRow(7, 'Carlos Gómez'))

        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/payments/7/history')
        expect(wrapper.vm.historyPayment.id).toBe(7)
        expect(wrapper.vm.paymentHistory).toHaveLength(1)
        expect(wrapper.vm.paymentHistory[0].month_label).toBe('Enero')

        wrapper.unmount()
    })

    it('keeps each current amount during bulk update when bulk amount is zero', async () => {
        const wrapper = mountComposable()
        wrapper.vm.statusCatalog = statusCatalogFixture()
        wrapper.vm.formData.year = 2026
        wrapper.vm.formData.month = 'january'
        wrapper.vm.bulkStatus = '9'
        wrapper.vm.bulkAmount = 0
        wrapper.vm.groupPayments = [{
            ...paymentRow(11, 'Jugador Uno'),
            inscription_deleted: false,
            january: 2,
            january_amount: 64000,
        }]
        apiMock.post.mockResolvedValue({
            data: {
                data: {
                    updated_count: 1,
                    updated_ids: [11],
                },
            },
        })

        await wrapper.vm.applyBulkPaymentStatus()

        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/payments/bulk-update', {
            payment_ids: [11],
            year: 2026,
            month: 'january',
            status: 9,
            amount: 0,
        })
        expect(wrapper.vm.groupPayments[0].january).toBe(9)
        expect(wrapper.vm.groupPayments[0].january_amount).toBe(64000)

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
                return Promise.resolve({ data: statusCatalogFixture() })
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
                month: currentMonthField(),
                status: null,
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
