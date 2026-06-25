import { mount } from '@vue/test-utils'
import { Form } from 'vee-validate'
import { describe, expect, it } from 'vitest'

import Checkbox from '@/components/form/Checkbox.vue'

function mountCheckbox(initialValue) {
    return mount(Form, {
        props: {
            initialValues: {
                titular: initialValue
            }
        },
        slots: {
            default: '<Checkbox name="titular" return-value-type="number" />'
        },
        global: {
            components: {
                Checkbox
            }
        }
    })
}

describe('Checkbox', () => {
    it('checks numeric fields when the form value is a string number', () => {
        const wrapper = mountCheckbox('1')

        expect(wrapper.find('input[type="checkbox"]').element.checked).toBe(true)
        wrapper.unmount()
    })

    it('unchecks numeric fields when the form value is zero-like', () => {
        const wrapper = mountCheckbox('0')

        expect(wrapper.find('input[type="checkbox"]').element.checked).toBe(false)
        wrapper.unmount()
    })
})
