import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const { apiMock, guardianStoreMock } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
    },
    guardianStoreMock: {
        user: { names: 'Laura Gómez' },
        getUser: vi.fn(),
    },
}));

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}));

vi.mock('@/store/guardian-auth', () => ({
    useGuardianAuth: () => guardianStoreMock,
}));

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}));

vi.mock('boneyard-js/vue', () => ({
    default: {
        name: 'Skeleton',
        props: ['loading', 'name'],
        template: '<div data-test="skeleton"><slot /></div>',
    },
}));

import GuardianDashboard from '@/pages/portal/guardians/GuardianDashboard.vue';

const mountPage = () => mount(GuardianDashboard, {
    global: {
        stubs: {
            Loader: true,
            RouterLink: {
                props: ['to'],
                template: '<a><slot /></a>',
            },
        },
    },
});

describe('GuardianDashboard', () => {
    beforeEach(() => {
        apiMock.get.mockReset();
        guardianStoreMock.getUser.mockReset();
        guardianStoreMock.user = { names: 'Laura Gómez' };
    });

    it('envuelve la vista con Boneyard y renderiza los jugadores al cargar', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                data: [
                    {
                        id: 44,
                        full_names: 'Mateo Rojas',
                        unique_code: 'GL-44',
                        photo_url: '/img/user.png',
                        school: { id: 9, name: 'Golapp FC' },
                        current_inscription: {
                            training_group: { name: 'Sub 12' },
                        },
                    },
                ],
            },
        });

        const wrapper = mountPage();

        expect(wrapper.find('[data-test="skeleton"]').exists()).toBe(true);

        await flushPromises();

        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/portal/acudientes/players');
        expect(wrapper.text()).toContain('Laura Gómez');
        expect(wrapper.text()).toContain('Golapp FC');
        expect(wrapper.text()).toContain('Mateo Rojas');
        expect(wrapper.text()).toContain('Sub 12');
    });

    it('mantiene el mensaje de error si la API falla', async () => {
        apiMock.get.mockRejectedValue({
            response: {
                data: { message: 'No fue posible consultar jugadores.' },
            },
        });

        const wrapper = mountPage();

        await flushPromises();

        expect(wrapper.text()).toContain('No fue posible consultar jugadores.');
    });
});
