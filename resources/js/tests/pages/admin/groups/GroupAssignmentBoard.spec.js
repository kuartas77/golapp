import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const { apiMock } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
}));

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}));

vi.mock('vue-draggable-next', () => ({
    VueDraggableNext: {
        name: 'draggable',
        props: ['list'],
        template: `
            <div class="draggable-stub">
                <slot />
            </div>
        `,
    },
}));

import GroupAssignmentBoard from '@/pages/admin/groups/shared/GroupAssignmentBoard.vue';

describe('GroupAssignmentBoard', () => {
    beforeEach(() => {
        apiMock.get.mockReset();
        apiMock.post.mockReset();
        vi.stubGlobal('showMessage', vi.fn());
        window.showMessage = globalThis.showMessage;
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('renders training selectors and loaded members', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                data: {
                    selectors: {
                        origin_groups: [{ value: '1', label: 'Provisional' }],
                        destination_groups: [{ value: '2', label: 'Avanzado' }],
                    },
                    panels: {
                        source: {
                            group_id: 1,
                            group_label: 'Provisional',
                            count: 1,
                            items: [{
                                id: 9,
                                full_names: 'Juan Perez',
                                category: 'SUB-13',
                                photo_url: '/img/user.webp',
                                search_text: 'Juan Perez SUB-13',
                            }],
                        },
                        destination: {
                            group_id: null,
                            group_label: null,
                            count: 0,
                            items: [],
                        },
                    },
                },
            },
        });

        const wrapper = mount(GroupAssignmentBoard, {
            props: { mode: 'training' },
            global: {
                stubs: {
                    Loader: { template: '<div class="loader-stub"></div>' },
                },
            },
        });

        await flushPromises();

        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/admin/training-groups/board', expect.any(Object));
        expect(wrapper.find('#origin_group_id').exists()).toBe(true);
        expect(wrapper.find('#destination_group_id').exists()).toBe(true);
        expect(wrapper.find('#competition_group_id').exists()).toBe(false);
        expect(wrapper.text()).toContain('Deportistas grupo de origen');
        expect(wrapper.text()).toContain('Deportistas grupo de destino');
        expect(wrapper.text()).toContain('Juan Perez');
        expect(wrapper.findAll('input[placeholder="Buscar..."]')).toHaveLength(0);
    });

    it('renders competition selector and local search inputs', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                data: {
                    selectors: {
                        groups: [{ value: '7', label: 'Equipo Azul (SUB-13)' }],
                    },
                    panels: {
                        source: {
                            group_id: null,
                            group_label: null,
                            count: 2,
                            items: [{
                                id: 12,
                                full_names: 'Carlos Mora',
                                category: 'SUB-13',
                                photo_url: '/img/user.webp',
                                search_text: 'Carlos Mora SUB-13',
                            }],
                        },
                        destination: {
                            group_id: 7,
                            group_label: 'Equipo Azul (SUB-13)',
                            count: 1,
                            items: [{
                                id: 14,
                                full_names: 'David Ruiz',
                                category: 'SUB-13',
                                photo_url: '/img/user.webp',
                                search_text: 'David Ruiz SUB-13',
                            }],
                        },
                    },
                },
            },
        });

        const wrapper = mount(GroupAssignmentBoard, {
            props: { mode: 'competition' },
            global: {
                stubs: {
                    Loader: { template: '<div class="loader-stub"></div>' },
                },
            },
        });

        await flushPromises();

        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/admin/competition-groups/board', expect.any(Object));
        expect(wrapper.find('#competition_group_id').exists()).toBe(true);
        expect(wrapper.find('#origin_group_id').exists()).toBe(false);
        expect(wrapper.findAll('input[placeholder="Buscar..."]')).toHaveLength(2);
        expect(wrapper.text()).toContain('Deportistas disponibles');
        expect(wrapper.text()).toContain('Equipo Azul (SUB-13)');
        expect(wrapper.text()).toContain('David Ruiz');
    });
});
