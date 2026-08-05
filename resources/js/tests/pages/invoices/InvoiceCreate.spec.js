import { flushPromises, mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const { axiosMock, routerPushMock, tutorialMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
    routerPushMock: vi.fn(),
    tutorialMock: {
        start: vi.fn(),
    },
}));

vi.mock('@/utils/axios', () => ({
    default: axiosMock,
}));

vi.mock('vue-router', () => ({
    useRoute: () => ({
        params: {
            inscription: '1',
        },
    }),
    useRouter: () => ({
        push: routerPushMock,
    }),
}));

vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => tutorialMock,
}));

vi.mock('@/tutorials/invoices', () => ({
    invoiceCreateTutorial: [],
}));

import InvoiceCreate from '@/pages/invoices/InvoiceCreate.vue';

const wrappers = [];

const loadPayload = {
    data: {
        inscription: {
            id: 1,
            training_group_id: 10,
            player: {
                full_names: 'Jugador Demo',
            },
            training_group: {
                name: 'Sub 10',
            },
        },
        pendingMonths: [],
        pendingUniformRequests: [],
        customCharges: [],
    },
};

const mountPage = async ({ configureGet } = {}) => {
    vi.stubGlobal('moneyFormat', (value) => `$${value}`);
    vi.stubGlobal('showMessage', vi.fn());
    vi.stubGlobal('Swal', {
        fire: vi.fn().mockResolvedValue({ isConfirmed: false }),
    });

    if (configureGet) {
        configureGet(axiosMock.get);
    } else {
        axiosMock.get.mockResolvedValue(loadPayload);
    }

    axiosMock.post.mockResolvedValue({
        data: {
            id: 55,
        },
    });

    const wrapper = mount(InvoiceCreate, {
        global: {
            config: {
                globalProperties: {
                    moneyFormat: (value) => `$${value}`,
                },
            },
            stubs: {
                PageTutorialOverlay: {
                    template: '<div />',
                },
                'flat-pickr': {
                    props: ['modelValue'],
                    template: '<input :value="modelValue" />',
                },
                CurrencyInput: {
                    props: ['modelValue'],
                    emits: ['update:modelValue'],
                    template: `
                        <input
                            :value="modelValue ?? ''"
                            @input="$emit('update:modelValue', Number($event.target.value))"
                        />
                    `,
                },
            },
        },
    });

    wrappers.push(wrapper);
    await flushPromises();
    await flushPromises();

    return wrapper;
};

describe('InvoiceCreate', () => {
    beforeEach(() => {
        axiosMock.get.mockReset();
        axiosMock.post.mockReset();
        routerPushMock.mockReset();
        tutorialMock.start.mockReset();
    });

    afterEach(() => {
        while (wrappers.length) {
            const wrapper = wrappers.pop();
            wrapper.unmount();
        }

        vi.unstubAllGlobals();
    });

    it('derives current totals from additional item values and submits normalized amounts', async () => {
        const wrapper = await mountPage();
        const state = wrapper.vm.$.setupState;

        expect(wrapper.get('h1').text()).toBe('Crear factura');
        state.addAdditionalItem();
        state.additionalItems[0].include = true;
        state.additionalItems[0].description = 'Canillera';
        state.additionalItems[0].quantity = '1';
        state.additionalItems[0].unit_price = '2000';

        await nextTick();

        expect(state.getLineTotal(state.additionalItems[0])).toBe(2000);
        expect(state.subtotal).toBe(2000);

        await state.submitInvoice();

        expect(axiosMock.post).toHaveBeenCalledWith('/api/v2/invoices', expect.objectContaining({
            idempotency_key: expect.stringMatching(/^invoice-create-1-/),
            items: [
                expect.objectContaining({
                    description: 'Canillera',
                    quantity: 1,
                    unit_price: 2000,
                }),
            ],
        }));
        expect(routerPushMock).toHaveBeenCalledWith({
            name: 'invoices.show',
            params: {
                id: 55,
            },
        });
    });

    it('focuses the confirm button so the invoice can be saved with Enter', async () => {
        const wrapper = await mountPage();

        await wrapper.find('form').trigger('submit');

        expect(window.Swal.fire).toHaveBeenCalledWith(expect.objectContaining({
            focusConfirm: true,
            confirmButtonText: '¡Sí, guardar!',
        }));
    });

    it('announces an initial load failure and recovers without leaving the page', async () => {
        const wrapper = await mountPage({
            configureGet: get => get
                .mockRejectedValueOnce({ response: { data: { message: 'Los conceptos no están disponibles.' } } })
                .mockResolvedValueOnce(loadPayload),
        });

        const alert = wrapper.get('[role="alert"]');
        expect(alert.text()).toContain('Los conceptos no están disponibles.')

        await alert.get('button').trigger('click');
        await flushPromises();

        expect(axiosMock.get).toHaveBeenCalledTimes(2);
        expect(wrapper.text()).toContain('Jugador Demo');
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    });

    it('keeps invoice inputs and the idempotency key after a failed save', async () => {
        const wrapper = await mountPage();
        const state = wrapper.vm.$.setupState;

        state.addAdditionalItem();
        state.additionalItems[0].include = true;
        state.additionalItems[0].description = 'Balón';
        state.additionalItems[0].quantity = 1;
        state.additionalItems[0].unit_price = 45000;
        axiosMock.post.mockRejectedValueOnce({
            response: { data: { message: 'El cargo ya fue facturado.' } },
        });

        await state.submitInvoice();
        const firstKey = axiosMock.post.mock.calls[0][1].idempotency_key;

        expect(wrapper.get('[role="alert"]').text()).toContain('El cargo ya fue facturado.');
        expect(state.additionalItems[0].description).toBe('Balón');

        axiosMock.post.mockRejectedValueOnce({
            response: { data: { message: 'Intenta nuevamente.' } },
        });
        await state.submitInvoice();

        expect(axiosMock.post.mock.calls[1][1].idempotency_key).toBe(firstKey);
    });
});
