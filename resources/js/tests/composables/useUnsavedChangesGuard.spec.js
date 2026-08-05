import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

const { leaveGuard } = vi.hoisted(() => ({ leaveGuard: { current: null } }))

vi.mock('vue-router', () => ({
    onBeforeRouteLeave: callback => {
        leaveGuard.current = callback
    },
}))

import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard'

const Harness = defineComponent({
    setup() {
        const dirty = ref(false)
        const saving = ref(false)
        const guard = useUnsavedChangesGuard({ isDirty: dirty, isSaving: saving })

        return { dirty, saving, ...guard }
    },
    template: '<div />',
})

describe('useUnsavedChangesGuard', () => {
    beforeEach(() => {
        leaveGuard.current = null
        window.Swal = { fire: vi.fn() }
    })

    afterEach(() => {
        delete window.Swal
    })

    it('allows clean navigation and consumes an explicit one-time bypass', async () => {
        const wrapper = mount(Harness)

        expect(await leaveGuard.current()).toBe(true)

        wrapper.vm.dirty = true
        wrapper.vm.skipGuardOnce()
        expect(await leaveGuard.current()).toBe(true)
        wrapper.unmount()
    })

    it('keeps the user editing unless discarding is confirmed', async () => {
        const wrapper = mount(Harness)
        wrapper.vm.dirty = true
        window.Swal.fire
            .mockResolvedValueOnce({ isConfirmed: false })
            .mockResolvedValueOnce({ isConfirmed: true })

        expect(await leaveGuard.current()).toBe(false)
        expect(await leaveGuard.current()).toBe(true)
        expect(window.Swal.fire).toHaveBeenCalledWith(expect.objectContaining({
            confirmButtonText: 'Salir sin guardar',
            cancelButtonText: 'Seguir editando',
            focusCancel: true,
        }))
        wrapper.unmount()
    })

    it('blocks navigation while a save is in progress', async () => {
        const wrapper = mount(Harness)
        wrapper.vm.dirty = true
        wrapper.vm.saving = true

        expect(await leaveGuard.current()).toBe(false)
        expect(window.Swal.fire).not.toHaveBeenCalled()
        wrapper.unmount()
    })
})
