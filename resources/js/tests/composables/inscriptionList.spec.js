import { mount } from '@vue/test-utils';
import { defineComponent, ref } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const { apiMock, routerMock } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        delete: vi.fn(),
    },
    routerMock: {
        push: vi.fn(),
    },
}));

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}));

vi.mock('vue-router', () => ({
    useRouter: () => routerMock,
}));

import useInscriptionConfig from '@/composables/inscription/inscriptionList';

const mountComposable = () => mount(defineComponent({
    setup() {
        return useInscriptionConfig(ref('2026'), ref(true));
    },
    template: '<div />',
}));

describe('inscription list states', () => {
    beforeEach(() => {
        apiMock.get.mockReset();
        apiMock.delete.mockReset();
        routerMock.push.mockReset();
    });

    it('exposes a recoverable load error and clears it after a successful request', async () => {
        const wrapper = mountComposable();
        const callback = vi.fn();

        apiMock.get.mockRejectedValueOnce({
            response: { data: { message: 'El servicio de inscripciones no respondió.' } },
        });

        await wrapper.vm.options.ajax({ draw: 1 }, callback);

        expect(wrapper.vm.globalError).toBe('El servicio de inscripciones no respondió.');
        expect(callback).toHaveBeenLastCalledWith({ data: [], recordsTotal: 0, recordsFiltered: 0 });

        apiMock.get.mockResolvedValueOnce({
            data: { data: [], recordsTotal: 0, recordsFiltered: 0 },
        });

        await wrapper.vm.options.ajax({ draw: 2 }, callback);

        expect(wrapper.vm.globalError).toBe('');
        wrapper.unmount();
    });
});
