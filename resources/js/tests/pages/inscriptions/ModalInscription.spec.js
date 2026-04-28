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

const mountModal = async (props = { inscription_id: null }) => {
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
});
