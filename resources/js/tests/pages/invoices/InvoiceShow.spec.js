import { flushPromises, mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock, routerPushMock, modalShowMock, modalDisposeMock } = vi.hoisted(() => ({
    axiosMock: {
        delete: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
    },
    routerPushMock: vi.fn(),
    modalShowMock: vi.fn(),
    modalDisposeMock: vi.fn(),
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { id: '91' } }),
    useRouter: () => ({ push: routerPushMock }),
}))
vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => ({ start: vi.fn() }),
}))
vi.mock('@/tutorials/invoices', () => ({ invoiceShowTutorial: [] }))
vi.mock('@/store/auth-user', () => ({
    useAuthUser: () => ({ hasRole: () => false }),
}))

import InvoiceShow from '@/pages/invoices/InvoiceShow.vue'

const invoicePayload = {
    id: 91,
    invoice_number: 'FAC-E2E-91',
    numbering_type: 'legacy',
    status: 'pending',
    student_name: 'Jugador Demo',
    year: 2026,
    issue_date: '2026-08-01',
    due_date: '2026-08-15',
    total_amount: 50000,
    paid_amount: 0,
    url_print: '/api/v2/invoices/FAC-E2E-91/print',
    training_group: { name: 'Sub 15' },
    creator: { name: 'Administrador' },
    items: [{
        id: 11,
        type: 'monthly',
        description: 'Mensualidad agosto',
        quantity: 1,
        unit_price: 50000,
        total: 50000,
        is_paid: false,
    }],
    payments: [],
    payment_requests: [],
}

const wrappers = []

const mountPage = async ({ configureGet } = {}) => {
    vi.stubGlobal('moneyFormat', value => `$${value}`)
    vi.stubGlobal('showMessage', vi.fn())
    vi.stubGlobal('Swal', {
        fire: vi.fn().mockResolvedValue({ isConfirmed: false }),
    })
    window.bootstrap = {
        Modal: vi.fn(class {
            show = modalShowMock
            dispose = modalDisposeMock
        }),
    }

    if (configureGet) {
        configureGet(axiosMock.get)
    } else {
        axiosMock.get.mockResolvedValue({ data: invoicePayload })
    }

    const wrapper = mount(InvoiceShow, {
        global: {
            config: {
                globalProperties: {
                    moneyFormat: value => `$${value}`,
                },
            },
            directives: {
                tooltip: {},
            },
            stubs: {
                PageTutorialOverlay: { template: '<div />' },
                'flat-pickr': {
                    props: ['modelValue'],
                    template: '<input :value="modelValue" />',
                },
            },
        },
    })

    wrappers.push(wrapper)
    await flushPromises()
    await flushPromises()

    return wrapper
}

describe('InvoiceShow financial recovery', () => {
    beforeEach(() => {
        axiosMock.delete.mockReset()
        axiosMock.get.mockReset()
        axiosMock.post.mockReset()
        routerPushMock.mockReset()
        modalShowMock.mockReset()
        modalDisposeMock.mockReset()
    })

    afterEach(() => {
        while (wrappers.length) {
            wrappers.pop().unmount()
        }

        vi.unstubAllGlobals()
    })

    it('announces a detail load failure and retries in place', async () => {
        const wrapper = await mountPage({
            configureGet: get => get
                .mockRejectedValueOnce({ response: { data: { message: 'La factura no está disponible.' } } })
                .mockResolvedValueOnce({ data: invoicePayload }),
        })

        const alert = wrapper.get('[role="alert"]')
        expect(alert.text()).toContain('La factura no está disponible.')

        await alert.get('button').trigger('click')
        await flushPromises()

        expect(axiosMock.get).toHaveBeenCalledTimes(2)
        expect(wrapper.get('h1').text()).toBe('Recibo de caja #FAC-E2E-91')
        expect(wrapper.text()).toContain('no constituye ni reemplaza una factura electrónica')
        expect(wrapper.find('[role="alert"]').exists()).toBe(false)
    })

    it('keeps the selected concepts and idempotency key after a failed payment', async () => {
        const wrapper = await mountPage()
        const state = wrapper.vm.$.setupState
        state.payment.paid_items = [11]
        await nextTick()
        state.updatePaymentAmount()
        axiosMock.post.mockRejectedValueOnce({
            response: { data: { message: 'El pago no pudo confirmarse.' } },
        })

        await state.submitPayment()
        const firstPayload = axiosMock.post.mock.calls[0][1]

        expect(firstPayload.idempotency_key).toMatch(/^invoice-payment-91-/)
        expect(firstPayload.amount).toBe(50000)
        expect(wrapper.get('[role="alert"]').text()).toContain('El pago no pudo confirmarse.')
        expect(state.payment.paid_items).toEqual([11])

        axiosMock.post.mockRejectedValueOnce({
            response: { data: { message: 'Intenta nuevamente.' } },
        })
        await state.submitPayment()

        expect(axiosMock.post.mock.calls[1][1].idempotency_key).toBe(firstPayload.idempotency_key)
    })

    it('uses an explicit, cancel-focused confirmation before annulling', async () => {
        const wrapper = await mountPage()
        const state = wrapper.vm.$.setupState

        await state.confirmDelete()

        expect(window.Swal.fire).toHaveBeenCalledWith(expect.objectContaining({
            title: '¿Anular recibo de caja #FAC-E2E-91?',
            confirmButtonText: 'Sí, anular recibo de caja',
            cancelButtonText: 'Conservar recibo de caja',
            focusCancel: true,
        }))
        expect(axiosMock.delete).not.toHaveBeenCalled()
    })

    it('shows the authorized range and locks the issue date for an electronic invoice', async () => {
        const wrapper = await mountPage({
            configureGet: get => get.mockResolvedValue({
                data: {
                    ...invoicePayload,
                    numbering_type: 'electronic',
                    number_range: {
                        resolution_number: '18764012345678',
                        resolution_date: '2026-01-01',
                        prefix: 'FE',
                        range_start: 1,
                        range_end: 500,
                        valid_from: '2026-01-01',
                        valid_until: '2026-12-31',
                    },
                },
            }),
        })

        expect(wrapper.text()).toContain('Resolución 18764012345678')
        expect(wrapper.get('h1').text()).toBe('Factura #FAC-E2E-91')
        expect(wrapper.text()).not.toContain('no constituye ni reemplaza una factura electrónica')
        expect(wrapper.get('#invoiceIssueDate').attributes('disabled')).toBeDefined()
    })
})
