import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { afterEach, describe, expect, it, vi } from 'vitest'

import { useDialogFocusTrap } from '@/composables/useDialogFocusTrap'

const Harness = defineComponent({
    setup() {
        const dialog = ref(null)
        const open = ref(false)
        const close = vi.fn(() => { open.value = false })
        useDialogFocusTrap(dialog, open, { onEscape: close })

        return { dialog, open, close }
    },
    template: `
        <div>
            <button id="origin" @click="open = true">Abrir</button>
            <div v-if="open" ref="dialog" role="dialog" tabindex="-1">
                <button id="first">Primero</button>
                <button id="last">Último</button>
            </div>
        </div>
    `,
})

describe('useDialogFocusTrap', () => {
    let wrapper

    afterEach(() => wrapper?.unmount())

    it('moves focus into the dialog, traps Tab and restores the trigger', async () => {
        wrapper = mount(Harness, { attachTo: document.body })
        const origin = wrapper.get('#origin')
        origin.element.focus()
        await origin.trigger('click')
        await wrapper.vm.$nextTick()

        expect(document.activeElement.id).toBe('first')

        wrapper.get('#last').element.focus()
        await wrapper.get('[role="dialog"]').trigger('keydown', { key: 'Tab' })
        expect(document.activeElement.id).toBe('first')

        await wrapper.get('[role="dialog"]').trigger('keydown', { key: 'Escape' })
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.close).toHaveBeenCalledOnce()
        expect(document.activeElement.id).toBe('origin')
    })
})
