import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))

import useFormSchool from '@/composables/admin/school/formSchool'

const Harness = defineComponent({
    setup() {
        return useFormSchool()
    },
    template: '<div />',
})

const validValues = {
    slug: 'escuela-demo',
    name: 'Escuela Demo',
    logo: null,
}

describe('useFormSchool submit lifecycle', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        axiosMock.post.mockReset()
        axiosMock.get.mockImplementation(() => new Promise(() => {}))
        vi.stubGlobal('Swal', { fire: vi.fn().mockResolvedValue({ isConfirmed: true }) })
        vi.stubGlobal('showMessage', vi.fn())
    })

    it('keeps the submit promise pending until the save request finishes', async () => {
        let resolvePost
        axiosMock.post.mockImplementation(() => new Promise((resolve) => {
            resolvePost = resolve
        }))

        const wrapper = mount(Harness)
        const submitPromise = wrapper.vm.submit(validValues, { setErrors: vi.fn() })
        let finished = false
        submitPromise.then(() => { finished = true })

        await Promise.resolve()
        await Promise.resolve()
        expect(axiosMock.post).toHaveBeenCalledOnce()
        expect(finished).toBe(false)

        resolvePost({ data: { success: true } })
        await submitPromise

        expect(finished).toBe(true)
        expect(showMessage).toHaveBeenCalledWith('Guardado correctamente.')
        wrapper.unmount()
    })

    it('exposes a useful global error when the API reports an unsuccessful save', async () => {
        axiosMock.post.mockResolvedValue({
            data: { success: false, message: 'La configuración no pudo guardarse.' },
        })

        const wrapper = mount(Harness)
        await wrapper.vm.submit(validValues, { setErrors: vi.fn() })

        expect(wrapper.vm.globalError).toBe('La configuración no pudo guardarse.')
        wrapper.unmount()
    })
})
