import { defineComponent, ref } from 'vue'
import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiGetMock, reloadTableMock, routerPushMock, state } = vi.hoisted(() => ({
    apiGetMock: vi.fn(),
    reloadTableMock: vi.fn(),
    routerPushMock: vi.fn(),
    state: {
        globalError: null,
    },
}))

vi.mock('@/utils/axios', () => ({
    default: {
        get: apiGetMock,
    },
}))

vi.mock('vue-router', () => ({
    useRouter: () => ({ push: routerPushMock }),
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
        apiGetMock.mockReset()
        reloadTableMock.mockReset()
        routerPushMock.mockReset()
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

    it('places page actions in the header and distributes filters across the available width', () => {
        state.globalError = ref('')
        const wrapper = mountPage()
        const toolbar = wrapper.get('[data-tour="invoices-index-filters"]')
        const headerActions = wrapper.get('[data-tour="invoices-index-actions"]')

        expect(toolbar.classes()).toEqual(expect.arrayContaining(['row', 'g-3', 'align-items-end']))
        expect(toolbar.findAll(':scope > div').map(column => column.classes())).toEqual([
            expect.arrayContaining(['col-12', 'invoice-status-filter']),
            expect.arrayContaining(['col-12', 'invoice-date-filter']),
        ])
        expect(toolbar.get('label[for="filterStatus"]').text()).toBe('Estado')
        expect(toolbar.get('label[for="filterDate"]').text()).toBe('Rango fecha facturación')
        expect(headerActions.text()).toContain('Crear factura')
        expect(headerActions.text()).toContain('Guía')
        expect(headerActions.findAll('.invoice-toolbar-action')).toHaveLength(2)

        wrapper.unmount()
    })

    it('loads inscriptions and continues to the existing invoice form', async () => {
        state.globalError = ref('')
        let resolveInscriptions
        apiGetMock.mockReturnValue(new Promise(resolve => {
            resolveInscriptions = resolve
        }))
        const wrapper = mountPage()

        await wrapper.get('[data-tour="invoices-index-actions"] button.btn-primary').trigger('click')
        expect(wrapper.text()).toContain('Cargando inscripciones')
        resolveInscriptions({
            data: {
                data: [{
                    id: 17,
                    unique_code: 'INS-0017',
                    player_name: 'Ana Zuluaga',
                    training_group_name: 'Sub 12',
                }],
            },
        })
        await flushPromises()

        expect(apiGetMock).toHaveBeenCalledWith('/api/v2/invoices/creation-inscriptions')
        expect(wrapper.get('[role="dialog"]').text()).toContain('Ana Zuluaga · INS-0017')
        const continueButton = wrapper.findAll('[role="dialog"] button').find(button => button.text() === 'Continuar')
        expect(continueButton.attributes('disabled')).toBeDefined()

        wrapper.getComponent({ name: 'CustomSelect2' }).vm.$emit('update:modelValue', '17')
        await wrapper.vm.$nextTick()
        expect(continueButton.attributes('disabled')).toBeUndefined()
        await wrapper.get('[role="dialog"] form').trigger('submit')

        expect(routerPushMock).toHaveBeenCalledWith({
            name: 'invoices.create',
            params: { inscription: '17' },
        })
        expect(wrapper.find('[role="dialog"]').exists()).toBe(false)
        wrapper.unmount()
    })

    it('shows retry and empty states in the inscription selector', async () => {
        state.globalError = ref('')
        apiGetMock
            .mockRejectedValueOnce({ response: { data: { message: 'No se pudo consultar.' } } })
            .mockResolvedValueOnce({ data: { data: [] } })
        const wrapper = mountPage()

        await wrapper.get('[data-tour="invoices-index-actions"] button.btn-primary').trigger('click')
        await flushPromises()
        const alert = wrapper.get('[role="dialog"] [role="alert"]')
        expect(alert.text()).toContain('No se pudo consultar.')

        await alert.get('button').trigger('click')
        await flushPromises()
        expect(wrapper.get('[role="dialog"]').text()).toContain('No hay inscripciones disponibles')
        expect(apiGetMock).toHaveBeenCalledTimes(2)
        wrapper.unmount()
    })
})
