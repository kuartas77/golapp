import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { ref } from 'vue';

const { apiMock } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        interceptors: {
            request: {
                use: vi.fn(),
            },
            response: {
                use: vi.fn(),
            },
        },
    },
}));

vi.mock('axios', () => ({
    default: {
        create: vi.fn(() => apiMock),
    },
}));

vi.mock('vue-router', async (importOriginal) => {
    const actual = await importOriginal();

    return {
        ...actual,
        useRoute: () => ({
            params: ref({ slug: 'escuela-sin-cupos' }).value,
        }),
    };
});

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}));

import PortalSchoolShow from '@/pages/portal/PortalSchoolShow.vue';

describe('PortalSchoolShow', () => {
    beforeEach(() => {
        apiMock.get.mockReset();
    });

    it('informa que no hay cupos y oculta el formulario al alcanzar el límite', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                data: {
                    school: {
                        id: 10,
                        name: 'Escuela Sin Cupos',
                        slug: 'escuela-sin-cupos',
                        inscriptions_enabled: true,
                        tutor_platform: false,
                        create_contract: false,
                        send_documents: false,
                    },
                    year: 2026,
                    inscriptionLimit: {
                        current: 10,
                        limit: 10,
                        remaining: 0,
                        is_full: true,
                    },
                    contracts: { available: [] },
                    links: {},
                    endpoints: {},
                    assets: {},
                    options: {},
                    recaptcha: { enabled: false },
                },
            },
        });

        const wrapper = mount(PortalSchoolShow, {
            global: {
                stubs: {
                    Loader: true,
                    PortalSchoolInscriptionModal: {
                        template: '<div data-test="inscription-modal" />',
                    },
                },
            },
        });

        await flushPromises();

        expect(wrapper.text()).toContain('El cupo límite de inscripciones para 2026 ya se ha alcanzado.');
        expect(wrapper.text()).toContain('Comunícate con Escuela Sin Cupos para que gestionen la disponibilidad de cupos.');
        expect(wrapper.text()).not.toContain('Realizar Inscripción');
        expect(wrapper.find('[data-test="inscription-modal"]').exists()).toBe(false);
    });

    it('muestra un error recuperable y vuelve a consultar la escuela', async () => {
        apiMock.get
            .mockRejectedValueOnce({
                response: { data: { message: 'El servicio no está disponible temporalmente.' } },
            })
            .mockResolvedValueOnce({
                data: {
                    data: {
                        school: {
                            id: 10,
                            name: 'Escuela Recuperada',
                            slug: 'escuela-sin-cupos',
                            inscriptions_enabled: false,
                            tutor_platform: false,
                            create_contract: false,
                            send_documents: false,
                        },
                        year: 2026,
                        inscriptionLimit: { is_full: false },
                        contracts: { available: [] },
                        links: {},
                        endpoints: {},
                        assets: {},
                        options: {},
                        recaptcha: { enabled: false },
                    },
                },
            });

        const wrapper = mount(PortalSchoolShow, {
            global: {
                stubs: {
                    PortalSchoolInscriptionModal: true,
                },
            },
        });

        await flushPromises();

        expect(wrapper.get('[role="alert"]').text()).toContain('El servicio no está disponible temporalmente.');

        await wrapper.get('[role="alert"] button').trigger('click');
        await flushPromises();

        expect(apiMock.get).toHaveBeenCalledTimes(2);
        expect(wrapper.text()).toContain('Escuela Recuperada');
        expect(wrapper.find('.content-state--error').exists()).toBe(false);
    });
});
