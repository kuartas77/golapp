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

    it('does not submit super-admin-only options from the school form', async () => {
        axiosMock.post.mockResolvedValue({ data: { success: true } })
        const wrapper = mount(Harness)

        await wrapper.vm.submit({
            ...validValues,
            CATEGORY_FORMAT: 'birth_year',
            tutor_platform: true,
            inscriptions_enabled: true,
            INSTRUCTOR_MONTH_LOCK_ENABLED: true,
        }, { setErrors: vi.fn() })

        const payload = axiosMock.post.mock.calls[0][1]
        expect(payload.has('CATEGORY_FORMAT')).toBe(false)
        expect(payload.has('tutor_platform')).toBe(false)
        expect(payload.has('inscriptions_enabled')).toBe(false)
        expect(payload.has('INSTRUCTOR_MONTH_LOCK_ENABLED')).toBe(false)
        wrapper.unmount()
    })

    it('does not submit legacy extra tariffs while group pricing is enabled', async () => {
        axiosMock.post.mockResolvedValue({ data: { success: true } })
        const wrapper = mount(Harness)

        await wrapper.vm.submit({
            ...validValues,
            training_group_monthly_payment_enabled: true,
            MONTHLY_PAYMENT_OPTION_1: 55000,
            MONTHLY_PAYMENT_OPTION_2: 60000,
            MONTHLY_PAYMENT_OPTION_3: 65000,
        }, { setErrors: vi.fn() })

        const payload = axiosMock.post.mock.calls[0][1]
        expect(payload.has('training_group_monthly_payment_enabled')).toBe(false)
        expect(payload.has('MONTHLY_PAYMENT_OPTION_1')).toBe(false)
        expect(payload.has('MONTHLY_PAYMENT_OPTION_2')).toBe(false)
        expect(payload.has('MONTHLY_PAYMENT_OPTION_3')).toBe(false)
        wrapper.unmount()
    })
})
