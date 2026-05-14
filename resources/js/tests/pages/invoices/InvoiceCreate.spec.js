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

vi.mock('axios', () => ({
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

const mountPage = async () => {
    vi.stubGlobal('moneyFormat', (value) => `$${value}`);
    vi.stubGlobal('showMessage', vi.fn());
    vi.stubGlobal('Swal', {
        fire: vi.fn(),
    });

    axiosMock.get.mockResolvedValue({
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
    });

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
});
