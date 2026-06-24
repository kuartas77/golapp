import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const { apiMock, settingsStore, authStore } = vi.hoisted(() => ({
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
        settings: {
            MONTHLY_PAYMENT: 50000,
            BROTHER_MONTHLY_PAYMENT: 40000,
            MONTHLY_PAYMENT_OPTION_1: 55000,
            MONTHLY_PAYMENT_OPTION_2: 0,
            MONTHLY_PAYMENT_OPTION_3: 65000,
        },
        getSettings: vi.fn().mockResolvedValue(undefined),
    },
    authStore: {
        hasSchoolPermission: vi.fn(),
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

vi.mock('@/store/auth-user', () => ({
    useAuthUser: () => authStore,
}));

import ModalInscription from '@/pages/inscriptions/ModalInscription.vue';

const wrappers = [];

const mountModal = async (
    props = { inscription_id: null, create_open: false, selected_year: 2026 },
    options = {},
) => {
    authStore.hasSchoolPermission.mockReturnValue(options.hasBillingPermission ?? true);

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
                    scholarship: '0',
                    brother_payment: 'false',
                    monthly_payment_type: 'MONTHLY_PAYMENT_OPTION_1',
                    monthly_payment_amount: 55000,
                    training_group_id: 2,
                    competition_groups: [],
                    photos: '0',
                    copy_identification_document: 'false',
                    eps_certificate: '0',
                    medic_certificate: 'false',
                    study_certificate: '0',
                    pre_inscription: 'false',
                },
            });
        }

        if (url === '/api/v2/inscriptions/3/edit') {
            return Promise.resolve({
                data: {
                    id: 3,
                    player_id: 25,
                    unique_code: 'PROV123',
                    player: {
                        full_names: 'Jugador Provisional',
                    },
                    start_date: '2026-04-10',
                    scholarship: false,
                    brother_payment: false,
                    monthly_payment_type: 'MONTHLY_PAYMENT',
                    monthly_payment_amount: 50000,
                    training_group_id: 1,
                    competition_groups: [],
                    photos: false,
                    copy_identification_document: false,
                    eps_certificate: false,
                    medic_certificate: false,
                    study_certificate: false,
                    pre_inscription: true,
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
                    template: '<div class="custom-select2-stub" :id="id" :data-select-id="id">{{ JSON.stringify({ modelValue, options, multiple }) }}</div>',
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
        authStore.hasSchoolPermission.mockReset();
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

    it('normalizes boolean values received as numbers and strings', async () => {
        const wrapper = await mountModal();
        const { normalizeBoolean } = wrapper.vm.$.setupState;

        expect(['1', 'true', 'yes', 'on', 1, true].every(normalizeBoolean)).toBe(true);
        expect(['0', 'false', 'no', 'off', 0, false, null, undefined].some(normalizeBoolean)).toBe(false);
    });

    it('opens explicitly in create mode', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2027 });

        await wrapper.setProps({ create_open: true });
        await flushPromises();

        expect(wrapper.__bootstrapModal.show).toHaveBeenCalled();
        expect(wrapper.vm.$.setupState.isEditing).toBe(false);
    });

    it('hides custom charges when billing is disabled', async () => {
        const wrapper = await mountModal(
            { inscription_id: null, create_open: false, selected_year: 2026 },
            { hasBillingPermission: false },
        );

        await wrapper.setProps({ create_open: true });
        await flushPromises();

        expect(wrapper.text()).not.toContain('Cargos personalizados');
        expect(apiMock.get).not.toHaveBeenCalledWith('/api/v2/admin/invoice-items-custom');

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
        }, { setErrors: vi.fn() });

        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/inscriptions', expect.not.objectContaining({
            custom_charges: expect.anything(),
        }));
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

    it('keeps API boolean fields unchecked when they return false-like strings', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2026 });

        await wrapper.setProps({ inscription_id: 1 });
        await flushPromises();
        await flushPromises();

        expect(wrapper.vm.$.setupState.form.values).toMatchObject({
            scholarship: false,
            brother_payment: false,
            photos: false,
            copy_identification_document: false,
            eps_certificate: false,
            medic_certificate: false,
            study_certificate: false,
            pre_inscription: false,
        });
    });

    it('shows configured monthly payment amounts and hides zero-value options', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2026 });

        expect(wrapper.vm.$.setupState.monthlyPaymentOptions).toEqual([
            { value: 'MONTHLY_PAYMENT', label: 'Mensualidad por defecto - $ 50.000' },
            { value: 'BROTHER_MONTHLY_PAYMENT', label: 'Mensualidad hermano - $ 40.000' },
            { value: 'MONTHLY_PAYMENT_OPTION_1', label: 'Mensualidad 5 dias - $ 55.000' },
            { value: 'MONTHLY_PAYMENT_OPTION_3', label: 'Mensualidad 3 dias - $ 65.000' },
        ]);
    });

    it('normalizes the training group id so the select can show its label when editing', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2026 });

        await wrapper.setProps({ inscription_id: 1 });
        await flushPromises();
        await flushPromises();

        expect(wrapper.vm.$.setupState.trainingGroups).toContainEqual({
            value: '2',
            label: 'Grupo definitivo',
        });
        expect(wrapper.vm.$.setupState.trainingGroups).not.toContainEqual({
            value: '1',
            label: 'Provisional',
        });
        expect(wrapper.vm.$.setupState.form.values.training_group_id).toBe('2');
    });

    it('hides provisional training group instead of showing its id when editing', async () => {
        const wrapper = await mountModal({ inscription_id: null, create_open: false, selected_year: 2026 });

        await wrapper.setProps({ inscription_id: 3 });
        await flushPromises();
        await flushPromises();

        expect(wrapper.vm.$.setupState.currentTrainingGroupId).toBe('1');
        expect(wrapper.vm.$.setupState.form.values.training_group_id).toBeNull();
        expect(wrapper.get('[data-select-id="training_group_id"]').text()).toContain('"modelValue":null');
        expect(wrapper.get('[data-select-id="training_group_id"]').text()).not.toContain('Provisional');
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
