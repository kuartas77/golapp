import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'

import CustomSelect2 from '@/components/form/CustomSelect2.vue'

const options = [
    { value: '10', label: 'Grupo A' },
    { value: '20', label: 'Grupo B' },
]

describe('CustomSelect2', () => {
    it('does not emit a user change when its value is synchronized from props', async () => {
        const wrapper = mount(CustomSelect2, {
            props: { modelValue: null, options },
        })

        await wrapper.setProps({ modelValue: '10' })

        expect(wrapper.emitted('update:modelValue')).toBeUndefined()
        expect(wrapper.text()).toContain('Grupo A')
    })

    it('emits changes made by the user', async () => {
        const wrapper = mount(CustomSelect2, {
            props: { modelValue: '10', options },
        })

        await wrapper.get('[role="combobox"]').trigger('click')
        await wrapper.findAll('[role="option"]')[1].trigger('click')

        expect(wrapper.emitted('update:modelValue')).toEqual([['20']])
    })

    it('opens from the keyboard and exposes Spanish action names', async () => {
        const wrapper = mount(CustomSelect2, {
            props: {
                modelValue: ['10'],
                options,
                multiple: true,
                ariaLabel: 'Grupos de entrenamiento',
            },
        })
        const combobox = wrapper.get('[role="combobox"]')

        expect(combobox.attributes('tabindex')).toBe('0')
        expect(combobox.attributes('aria-label')).toBe('Grupos de entrenamiento')
        expect(wrapper.get('button[aria-label="Quitar Grupo A"]').exists()).toBe(true)
        expect(wrapper.get('button[aria-label="Limpiar selección"]').exists()).toBe(true)

        await combobox.trigger('keydown', { key: 'Enter' })

        expect(combobox.attributes('aria-expanded')).toBe('true')
        expect(wrapper.get('[role="listbox"]').attributes('id')).toBe('select2-listbox')
    })
})
