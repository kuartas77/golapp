import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const { apiMock, settingsStore } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
        interceptors: {
            request: {
                use: vi.fn(),
            },
            response: {
                use: vi.fn(),
            },
        },
    },
    settingsStore: {
        groups: [
            { id: 1, name: 'Provisional' },
            { id: 2, name: 'Grupo definitivo' },
        ],
        competition_groups: [],
        all_groups: [
            { id: 1, name: 'Provisional' },
            { id: 2, name: 'Grupo definitivo' },
        ],
        getSettings: vi.fn().mockResolvedValue(undefined),
    },
}));

vi.mock('axios', () => ({
    default: {
        create: vi.fn(() => apiMock),
    },
}));

vi.mock('@/store/settings-store', () => ({
    useSetting: () => settingsStore,
}));

import ModalInscription from '@/pages/inscriptions/ModalInscription.vue';

const wrappers = [];

const mountModal = async (props = { inscription_id: null, create_open: false, selected_year: 2026 }) => {
    const bootstrapModal = {
        show: vi.fn(),
        hide: vi.fn(),
    };
    const BootstrapModal = vi.fn(function MockBootstrapModal() {
        return bootstrapModal;
    });

    vi.stubGlobal('showMessage', vi.fn());
    vi.stubGlobal('modalHidden', vi.fn());
    vi.stubGlobal('bootstrap', {
        Modal: BootstrapModal,
    });
    window.bootstrap = globalThis.bootstrap;

    apiMock.get.mockImplementation((url) => {
        if (url === '/api/v2/admin/invoice-items-custom') {
            return Promise.resolve({ data: [] });
        }

        if (url === '/api/v2/inscriptions/1/edit') {
            return Promise.resolve({
                data: {
                    id: 1,
                    player_id: 25,
                    unique_code: 'ABC123',
                    player: {
                        full_names: 'Jugador Demo',
                    },
                    start_date: '2026-04-10',
                    scholarship: false,
                    brother_payment: false,
                    monthly_payment_type: 'MONTHLY_PAYMENT_OPTION_1',
                    monthly_payment_amount: 55000,
                    training_group_id: 2,
                    competition_groups: [],
                    photos: false,
                    copy_identification_document: false,
                    eps_certificate: false,
                    medic_certificate: false,
                    study_certificate: false,
                    pre_inscription: false,
                },
            });
        }

        return Promise.reject(new Error(`Unexpected GET ${url}`));
    });

    apiMock.post.mockResolvedValue({
        data: {
            success: true,
        },
    });

    const wrapper = mount(ModalInscription, {
        attachTo: document.body,
        props,
        global: {
            stubs: {
                checkbox: {
                    props: ['name', 'label'],
                    template: '<input :name="name" type="checkbox" />',
                },
                CustomSelect2: {
                    props: ['id', 'modelValue', 'options', 'multiple'],
                    template: '<select :id="id"></select>',
                },
                TypeAhead: {
                    props: ['modelValue'],
                    template: '<input :value="modelValue" />',
                },
                'flat-pickr': {
                    props: ['modelValue'],
                    template: '<input :value="modelValue" />',
                },
            },
        },
    });

    wrappers.push(wrapper);
    await flushPromises();
    await flushPromises();

    wrapper.__bootstrapModal = bootstrapModal;

    return wrapper;
};

describe('ModalInscription', () => {
    beforeEach(() => {
        apiMock.get.mockReset();
        apiMock.post.mockReset();
        settingsStore.getSettings.mockClear();
    });

    afterEach(() => {
        while (wrappers.length) {
            const wrapper = wrappers.pop();
            wrapper.unmount();
        }

        vi.unstubAllGlobals();
    });

    it('normalizes pre_inscription changes as booleans', async () => {
        const wrapper = await mountModal();
        const { onPreInscriptionInput, currentPreInscription } = wrapper.vm.$.setupState;
        const handleChange = vi.fn();

        onPreInscriptionInput({ target: { checked: true } }, handleChange);
        onPreInscriptionInput({ target: { checked: false } }, handleChange);

        expect(handleChange).toHaveBeenNthCalledWith(1, true);
        expect(handleChange).toHaveBeenNthCalledWith(2, false);
        expect(currentPreInscription).toBe(false);
    });

    it('opens explicitly in create mode', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2027 });

        await wrapper.setProps({ create_open: true });
        await flushPromises();

        expect(wrapper.__bootstrapModal.show).toHaveBeenCalled();
        expect(wrapper.vm.$.setupState.isEditing).toBe(false);
    });

    it('marks the form as a reactivation when search_unique_code returns a retired inscription', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: true, selected_year: 2026 });

        apiMock.get.mockImplementation((url) => {
            if (url === '/api/v2/autocomplete/search_unique_code?unique=true') {
                return Promise.resolve({
                    data: {
                        data: {
                            id: 25,
                            full_names: 'Jugador Reactivado',
                            reactivation_inscription: {
                                id: 7,
                                start_date: '2026-02-01',
                                training_group_id: 2,
                                competition_groups: [],
                                scholarship: true,
                                brother_payment: true,
                                monthly_payment_type: null,
                                monthly_payment_amount: null,
                                pre_inscription: true,
                                photos: true,
                                copy_identification_document: true,
                                eps_certificate: false,
                                medic_certificate: true,
                                study_certificate: false,
                            },
                        },
                    },
                });
            }

            if (url === '/api/v2/admin/invoice-items-custom') {
                return Promise.resolve({ data: [] });
            }

            if (url === '/api/v2/autocomplete/list_code_unique?trashed=true') {
                return Promise.resolve({ data: { data: [] } });
            }

            return Promise.reject(new Error(`Unexpected GET ${url}`));
        });

        await wrapper.vm.$.setupState.onChangeCode('ABC123');
        await flushPromises();

        expect(wrapper.vm.$.setupState.isReactivationMode).toBe(true);
        expect(wrapper.text()).toContain('Se reactivará una inscripción retirada');
        expect(wrapper.find('#start_date').element.value).toBe('2026-02-01');
        expect(wrapper.vm.$.setupState.form.values.monthly_payment_type).toBe('BROTHER_MONTHLY_PAYMENT');
    });

    it('loads the monthly payment type when editing', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2026 });

        await wrapper.setProps({ inscription_id: 1 });
        await flushPromises();
        await flushPromises();

        expect(wrapper.vm.$.setupState.form.values.monthly_payment_type).toBe('MONTHLY_PAYMENT_OPTION_1');
    });

    it('submits the selected monthly payment type', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2026 });
        const actions = { setErrors: vi.fn() };

        await wrapper.vm.$.setupState.submit({
            id: null,
            player_id: 25,
            unique_code: 'ABC123',
            player_name: 'Jugador Demo',
            start_date: '2026-04-10',
            scholarship: false,
            brother_payment: false,
            monthly_payment_type: 'MONTHLY_PAYMENT_OPTION_2',
            training_group_id: 2,
            competition_groups: [],
            photos: false,
            copy_identification_document: false,
            eps_certificate: false,
            medic_certificate: false,
            study_certificate: false,
            pre_inscription: false,
        }, actions);

        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/inscriptions', expect.objectContaining({
            monthly_payment_type: 'MONTHLY_PAYMENT_OPTION_2',
        }));
    });
});
