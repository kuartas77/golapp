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

const mountComposable = ({ canManage = true, canCreateInvoice = null, canAddCustomCharges = null } = {}) => mount(defineComponent({
    setup() {
        return useInscriptionConfig(
            ref('2026'),
            ref(canManage),
            null,
            canCreateInvoice === null ? null : ref(canCreateInvoice),
            canAddCustomCharges === null ? null : ref(canAddCustomCharges),
        );
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

    it('renders only read actions and custom charges when inscriptions cannot be managed', () => {
        const wrapper = mountComposable({
            canManage: false,
            canCreateInvoice: false,
            canAddCustomCharges: true,
        });
        const actionsColumn = wrapper.vm.options.columns.at(-1);
        const html = actionsColumn.render(1, 'display', {
            id: 1,
            unique_code: 'PLY-1',
            url_destroy: '/inscriptions/1',
            url_impression: '/export/inscription/1',
            url_show: '/inscriptions/1',
        });

        expect(html).toContain('Agregar cargos');
        expect(html).toContain('Imprimir inscripción');
        expect(html).not.toContain('Modificar inscripción');
        expect(html).not.toContain('Retirar inscripción');
        expect(html).not.toContain('Crear factura');
        expect(html).not.toContain('QR asistencia');

        wrapper.unmount();
    });

    it('removes the global search control while preserving column searching', () => {
        const wrapper = mountComposable();

        expect(wrapper.vm.options.layout.topEnd).toBeNull();
        expect(wrapper.vm.options.columns[5]).toMatchObject({
            name: 'full_names',
            searchable: true,
        });
        expect(wrapper.vm.onNameFilterChange).toBeTypeOf('function');

        wrapper.unmount();
    });
});
