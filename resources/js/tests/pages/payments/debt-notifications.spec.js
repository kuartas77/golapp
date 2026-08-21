import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, dataTableReload, settingsStore, recoverableTable } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
    dataTableReload: vi.fn(),
    settingsStore: {
        groups: [],
        normal_training_groups: [{ id: 5, name: 'Grupo A', full_group: 'Grupo A' }],
        categories: [{ category: 'SUB-12' }],
    },
    recoverableTable: {
        globalError: '',
        tableKey: 0,
        clearError: vi.fn(),
        handleError: vi.fn(),
        reloadTable: vi.fn(),
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

vi.mock('@/composables/useRecoverableDataTable', () => ({
    useRecoverableDataTable: () => recoverableTable,
}))

import DebtNotifications from '@/pages/payments/DebtNotifications.vue'

const mountPage = () => shallowMount(DebtNotifications, {
    global: {
        stubs: {
            panel: { template: '<section><slot name="body" /></section>' },
            breadcrumb: true,
            RouterLink: true,
            Form: true,
            Field: true,
            ErrorMessage: true,
            ContentState: true,
            DatatableTemplate: {
                props: ['id'],
                template: '<div :id="id"></div>',
                setup(_, { expose }) {
                    expose({ table: { dt: { ajax: { reload: dataTableReload } } } })
                },
            },
        },
    },
})

describe('debt notifications page', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        apiMock.post.mockReset()
        dataTableReload.mockReset()
        recoverableTable.clearError.mockReset()
        recoverableTable.handleError.mockReset()
        recoverableTable.reloadTable.mockReset()
        recoverableTable.globalError = ''
        apiMock.get.mockResolvedValue({
            data: {
                months: [{ value: 'january', label: 'Enero' }],
            },
        })
        apiMock.post.mockResolvedValue({
            data: {
                message: 'Las 2 notificaciones fueron encoladas correctamente.',
                data: { queued_count: 2, skipped_count: 0 },
            },
        })
        window.Swal = {
            fire: vi.fn().mockResolvedValue({ isConfirmed: true }),
        }
        window.moneyFormat = vi.fn((value) => `$${value}`)
    })

    it('starts without querying debtors and loads the shared month catalog', async () => {
        const wrapper = mountPage()
        await vi.waitFor(() => expect(apiMock.get).toHaveBeenCalledWith('/api/v2/payments/status-catalog'))

        expect(wrapper.vm.hasSearched).toBe(false)
        expect(wrapper.vm.months).toEqual([{ value: 'january', label: 'Enero' }])
        expect(wrapper.vm.initialValues.month).toBe([
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december',
        ][new Date().getMonth()])
        expect(apiMock.get).not.toHaveBeenCalledWith('/api/v2/payments/debt-notifications', expect.anything())
    })

    it('selects only notifiable rows from the visible page', async () => {
        const wrapper = mountPage()
        const validRow = { payment_id: 11, player_name: 'Laura Perez', can_notify: true }
        const invalidRow = { payment_id: 12, player_name: 'Carlos Ruiz', can_notify: false }

        wrapper.vm.hasSearched = true
        await wrapper.vm.$nextTick()

        const table = wrapper.find('#debt-notifications-table').element
        const validCheckbox = document.createElement('input')
        const invalidCheckbox = document.createElement('input')
        validCheckbox.dataset.debtPaymentId = '11'
        invalidCheckbox.dataset.debtPaymentId = '12'
        table.append(validCheckbox, invalidCheckbox)

        wrapper.vm.visibleRows = [validRow, invalidRow]
        wrapper.vm.toggleRow(invalidRow)
        expect(wrapper.vm.selectedIds).toEqual([])

        wrapper.vm.selectedIds = [99]
        wrapper.vm.toggleVisibleRows({ target: { checked: true } })
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.selectedIds).toEqual([99, 11])
        expect(wrapper.vm.allVisibleSelected).toBe(true)
        expect(validCheckbox.checked).toBe(true)
        expect(invalidCheckbox.checked).toBe(false)

        wrapper.vm.toggleVisibleRows({ target: { checked: false } })
        expect(wrapper.vm.selectedIds).toEqual([99])
    })

    it('explains how selection works across pages', async () => {
        const wrapper = mountPage()

        wrapper.vm.hasSearched = true
        await wrapper.vm.$nextTick()

        expect(wrapper.text()).toContain('“Seleccionar todos” aplica a la página visible')
        expect(wrapper.text()).toContain('la selección se conservará')
        expect(wrapper.text()).toContain('al cambiar los filtros, se limpiará')
    })

    it('preserves selection while paginating and sends the selected filters to the server table', async () => {
        const wrapper = mountPage()
        wrapper.vm.filters.month = 'january'
        wrapper.vm.filters.search = 'Laura'
        wrapper.vm.filters.category = 'SUB-12'
        wrapper.vm.filters.training_group_id = 5
        wrapper.vm.selectedIds = [11]
        apiMock.get.mockResolvedValueOnce({
            data: {
                data: [{ payment_id: 11, can_notify: true }],
                recordsTotal: 1,
                recordsFiltered: 1,
            },
        })
        const callback = vi.fn()

        await wrapper.vm.tableOptions.ajax({ draw: 3, start: 0, length: 10 }, callback)

        expect(wrapper.vm.selectedIds).toEqual([11])
        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/payments/debt-notifications', expect.objectContaining({
            params: expect.objectContaining({
                month: 'january',
                search: 'Laura',
                category: 'SUB-12',
                training_group_id: 5,
            }),
        }))
        expect(callback).toHaveBeenCalledWith(expect.objectContaining({ recordsFiltered: 1 }))
    })

    it('does not forward the DataTables search object when the custom filter is empty', async () => {
        const wrapper = mountPage()
        wrapper.vm.filters.month = 'january'
        apiMock.get.mockResolvedValueOnce({
            data: {
                data: [],
                recordsTotal: 0,
                recordsFiltered: 0,
            },
        })

        await wrapper.vm.tableOptions.ajax({
            draw: 1,
            start: 0,
            length: 10,
            search: { value: '', regex: false },
        }, vi.fn())

        const requestOptions = apiMock.get.mock.calls.at(-1)[1]
        expect(requestOptions.params).not.toHaveProperty('search')
        expect(requestOptions.params.month).toBe('january')
    })

    it('confirms and sends one batch with the selected payment ids', async () => {
        const wrapper = mountPage()
        wrapper.vm.hasSearched = true
        wrapper.vm.filters.month = 'january'
        wrapper.vm.selectedIds = [11, 12]
        await wrapper.vm.$nextTick()

        await wrapper.vm.confirmSend()

        expect(window.Swal.fire).toHaveBeenCalledWith(expect.objectContaining({
            title: 'Enviar notificaciones de deuda',
            showCancelButton: true,
        }))
        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/payments/debt-notifications/send', {
            month: 'january',
            payment_ids: [11, 12],
        })
        expect(wrapper.vm.selectedIds).toEqual([])
        expect(dataTableReload).toHaveBeenCalledWith(null, false)
    })
})
