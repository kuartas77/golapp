import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import FormSubmitActions from '@/components/form/FormSubmitActions.vue'

describe('FormSubmitActions', () => {
    it('blocks both actions and communicates progress while submitting', async () => {
        const wrapper = mount(FormSubmitActions, {
            props: { submitting: true },
        })

        const buttons = wrapper.findAll('button')

        expect(buttons).toHaveLength(2)
        expect(buttons.every((button) => button.attributes('disabled') !== undefined)).toBe(true)
        expect(wrapper.get('button[type="submit"]').attributes('aria-busy')).toBe('true')
        expect(wrapper.text()).toContain('Guardando...')

        await buttons[0].trigger('click')
        expect(wrapper.emitted('cancel')).toBeUndefined()
    })

    it('supports a submit-only layout and emits cancel when available', async () => {
        const wrapper = mount(FormSubmitActions, {
            props: {
                showCancel: false,
                wrapperClass: 'text-center',
                submitLabel: 'Actualizar configuración',
            },
        })

        expect(wrapper.classes()).toContain('text-center')
        expect(wrapper.findAll('button')).toHaveLength(1)
        expect(wrapper.text()).toContain('Actualizar configuración')

        await wrapper.setProps({ showCancel: true })
        await wrapper.get('button[type="button"]').trigger('click')
        expect(wrapper.emitted('cancel')).toHaveLength(1)
    })
})
