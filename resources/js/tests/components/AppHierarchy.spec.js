import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'

import AppButton from '@/components/general/AppButton.vue'
import AppPageHeader from '@/components/general/AppPageHeader.vue'

describe('shared visual hierarchy', () => {
    it('exposes a semantic page title, supporting copy and an action region', () => {
        const wrapper = mount(AppPageHeader, {
            props: {
                title: 'Facturas',
                subtitle: 'Consulta los movimientos de la escuela.',
                icon: 'fa fa-file-invoice',
            },
            slots: {
                actions: '<button type="button">Actualizar</button>',
            },
        })

        expect(wrapper.get('header').exists()).toBe(true)
        expect(wrapper.get('h1').text()).toBe('Facturas')
        expect(wrapper.text()).toContain('Consulta los movimientos de la escuela.')
        expect(wrapper.get('.app-page-header__icon').attributes('aria-hidden')).toBe('true')
        expect(wrapper.get('.app-page-header__actions button').text()).toBe('Actualizar')
    })

    it('does not render an empty action region', () => {
        const wrapper = mount(AppPageHeader, { props: { title: 'Detalle' } })

        expect(wrapper.find('.app-page-header__actions').exists()).toBe(false)
    })

    it('announces loading, prevents duplicate actions and preserves configured variants', async () => {
        const onClick = vi.fn()
        const wrapper = mount(AppButton, {
            props: {
                loading: true,
                loadingLabel: 'Guardando...',
                variant: 'outline-danger',
                size: 'sm',
                onClick,
            },
            slots: { default: 'Guardar' },
        })

        expect(wrapper.get('button').attributes('disabled')).toBeDefined()
        expect(wrapper.get('button').attributes('aria-busy')).toBe('true')
        expect(wrapper.get('button').classes()).toContain('btn-outline-danger')
        expect(wrapper.get('button').classes()).toContain('btn-sm')
        expect(wrapper.text()).toBe('Guardando...')

        await wrapper.get('button').trigger('click')
        expect(onClick).not.toHaveBeenCalled()
    })
})
