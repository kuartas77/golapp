import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { reloadTableMock, state } = vi.hoisted(() => ({
    reloadTableMock: vi.fn(),
    state: {
        globalError: null,
    },
}))

vi.mock('@/composables/invoices/invoicesList', () => ({
    default: () => ({
        options: {},
        invoives_table: ref(null),
        filterDate: ref(''),
        clearDate: vi.fn(),
        onClickRow: vi.fn(),
        reloadTable: reloadTableMock,
        invoiceNumberFilter: ref(''),
        studentNameFilter: ref(''),
        trainingGroupFilter: ref(''),
        groupOptions: ref([]),
        groupOptionsLoaded: ref(true),
        globalError: state.globalError,
        applyInvoiceNumberFilter: vi.fn(),
        applyStudentNameFilter: vi.fn(),
        applyTrainingGroupFilter: vi.fn(),
    }),
}))

vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => ({ start: vi.fn() }),
}))
vi.mock('@/tutorials/invoices', () => ({ invoicesIndexTutorial: [] }))

import Invoices from '@/pages/invoices/Invoices.vue'

const DatatableStub = defineComponent({
    template: '<table><slot name="thead" /></table>',
})

function mountPage() {
    return mount(Invoices, {
        global: {
            stubs: {
                panel: { template: '<section><slot name="header" /><slot name="body" /></section>' },
                breadcrumb: { template: '<div />' },
                PageTutorialOverlay: { template: '<div />' },
                DatatableTemplate: DatatableStub,
                flatPickr: { template: '<input />' },
            },
        },
    })
}

describe('Invoices list states', () => {
    beforeEach(() => {
        reloadTableMock.mockReset()
        state.globalError = ref('El servicio de facturación no está disponible.')
    })

    it('announces load errors, offers retry and explains page-scoped totals', async () => {
        const wrapper = mountPage()

        expect(wrapper.get('h1').text()).toBe('Facturas')
        const alert = wrapper.get('[role="alert"]')
        expect(alert.text()).toContain('El servicio de facturación no está disponible.')
        await alert.get('button').trigger('click')
        expect(reloadTableMock).toHaveBeenCalledOnce()

        state.globalError.value = ''
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('Totales de esta página:')
        expect(wrapper.text()).toContain('Acciones')
        wrapper.unmount()
    })
})
