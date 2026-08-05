import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import ContentState from '@/components/general/ContentState.vue';

describe('ContentState', () => {
    it('renders a polite empty state with useful defaults', () => {
        const wrapper = mount(ContentState);

        expect(wrapper.attributes('role')).toBe('status');
        expect(wrapper.attributes('aria-live')).toBe('polite');
        expect(wrapper.text()).toContain('Aún no hay información');
        expect(wrapper.text()).toContain('Los datos aparecerán aquí cuando estén disponibles.');
    });

    it('announces errors and emits the retry action', async () => {
        const wrapper = mount(ContentState, {
            props: {
                type: 'error',
                title: 'No fue posible consultar',
                message: 'Revisa tu conexión.',
                actionLabel: 'Reintentar',
            },
        });

        expect(wrapper.attributes('role')).toBe('alert');
        expect(wrapper.attributes('aria-live')).toBe('assertive');

        await wrapper.get('button').trigger('click');

        expect(wrapper.emitted('action')).toHaveLength(1);
    });

    it('uses a status role without exposing an action while loading', () => {
        const wrapper = mount(ContentState, {
            props: { type: 'loading' },
        });

        expect(wrapper.attributes('role')).toBe('status');
        expect(wrapper.find('.spinner-border').exists()).toBe(true);
        expect(wrapper.find('button').exists()).toBe(false);
    });
});
