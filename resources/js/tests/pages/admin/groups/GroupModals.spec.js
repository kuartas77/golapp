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
        users: [],
        year_active: [],
        schedules: [],
        categories: [],
        tournaments: [],
        getGroupSettings: vi.fn().mockResolvedValue(undefined),
    },
}));

vi.mock('axios', () => ({
    default: {
        create: vi.fn(() => apiMock),
    },
}));

vi.mock('@/store/settings-store', () => ({
    useSettingGroups: () => settingsStore,
}));

import ModalCompetitionGroup from '@/pages/admin/groups/competition/ModalCompetitionGroup.vue';
import ModalTrainingGroup from '@/pages/admin/groups/training/ModalTrainingGroup.vue';

const wrappers = [];

const bootstrapModalFactory = () => ({
    show: vi.fn(),
    hide: vi.fn(),
});

const mountModal = async (component, props = { id: null }) => {
    const BootstrapModal = vi.fn(function MockBootstrapModal() {
        return bootstrapModalFactory();
    });

    vi.stubGlobal('showMessage', vi.fn());
    vi.stubGlobal('modalHidden', vi.fn());
    vi.stubGlobal('bootstrap', {
        Modal: BootstrapModal,
    });
    window.bootstrap = globalThis.bootstrap;

    const wrapper = mount(component, {
        attachTo: document.body,
        props,
        global: {
            mocks: {
                $handleBackendErrors: vi.fn(),
            },
            stubs: {
                inputField: {
                    props: ['name', 'label'],
                    template: '<input :name="name" :aria-label="label" />',
                },
                CustomSelect2: {
                    name: 'CustomSelect2',
                    props: ['id', 'modelValue', 'options'],
                    template: '<div class="custom-select2-stub" :data-select-id="id">{{ JSON.stringify({ modelValue, options }) }}</div>',
                },
                CustomMultiSelect: {
                    name: 'CustomMultiSelect',
                    props: ['id', 'modelValue', 'options'],
                    template: '<div class="custom-multiselect-stub" :data-select-id="id">{{ JSON.stringify({ modelValue, options }) }}</div>',
                },
            },
        },
    });

    wrappers.push(wrapper);
    await flushPromises();

    return wrapper;
};

describe('Admin group modals', () => {
    beforeEach(() => {
        apiMock.get.mockReset();
        apiMock.post.mockReset();
        settingsStore.getGroupSettings.mockClear();
        settingsStore.users = [];
        settingsStore.year_active = [];
        settingsStore.schedules = [];
        settingsStore.categories = [];
        settingsStore.tournaments = [];
    });

    afterEach(() => {
        while (wrappers.length) {
            wrappers.pop().unmount();
        }

        vi.unstubAllGlobals();
    });

    it('defaults the training group activity year to the current year on create', async () => {
        const currentYear = String(new Date().getFullYear());
        settingsStore.year_active = [currentYear];

        const wrapper = await mountModal(ModalTrainingGroup);

        expect(wrapper.get('select#year_active').element.value).toBe(currentYear);
    });

    it('rebuilds legacy training group categories and selected labels on edit', async () => {
        const currentYear = String(new Date().getFullYear());
        settingsStore.year_active = [currentYear];

        apiMock.get.mockImplementation((url) => {
            if (url === '/api/v2/admin/training_groups/44') {
                return Promise.resolve({
                    data: {
                        data: {
                            id: 44,
                            name: 'Grupo Avanzado',
                            stage: 'Cancha Norte',
                            year_active: Number(currentYear),
                            years: ['SUB-13', 'SUB-15'],
                            category: [],
                            explode_days: ['Lunes', 'Miércoles'],
                            explode_schedules: ['08:00 AM - 09:00 AM'],
                            instructors: [{ id: 99, name: 'Instructor Histórico' }],
                        },
                    },
                });
            }

            return Promise.reject(new Error(`Unexpected GET ${url}`));
        });

        const wrapper = await mountModal(ModalTrainingGroup);

        await wrapper.setProps({ id: '44' });
        await flushPromises();
        await flushPromises();

        expect(wrapper.get('[data-select-id="years"]').text()).toContain('SUB-13');
        expect(wrapper.get('[data-select-id="years"]').text()).toContain('SUB-15');
        expect(wrapper.get('[data-select-id="user_id"]').text()).toContain('Instructor Histórico');
        expect(wrapper.get('[data-select-id="schedules"]').text()).toContain('08:00 AM - 09:00 AM');
        expect(wrapper.get('select#year_active').element.value).toBe(currentYear);
    });

    it('keeps competition group labels available even when the active catalog no longer has them', async () => {
        settingsStore.categories = [{ value: 'SUB-17', label: 'SUB-17' }];

        apiMock.get.mockImplementation((url) => {
            if (url === '/api/v2/admin/competition_groups/12') {
                return Promise.resolve({
                    data: {
                        data: {
                            id: 12,
                            name: 'Competencia A',
                            tournament_id: 77,
                            user_id: 55,
                            year: 'SUB-17',
                            professor: { id: 55, name: 'Profesor Retirado' },
                            tournament: { id: 77, name: 'Copa Antigua' },
                        },
                    },
                });
            }

            return Promise.reject(new Error(`Unexpected GET ${url}`));
        });

        const wrapper = await mountModal(ModalCompetitionGroup);

        await wrapper.setProps({ id: '12' });
        await flushPromises();
        await flushPromises();

        expect(wrapper.get('[data-select-id="user_id"]').text()).toContain('Profesor Retirado');
        expect(wrapper.get('[data-select-id="tournament_id"]').text()).toContain('Copa Antigua');
    });
});
