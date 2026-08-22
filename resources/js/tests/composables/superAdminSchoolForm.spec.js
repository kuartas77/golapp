import { mount } from '@vue/test-utils'
import { defineComponent, nextTick } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { axiosMock, routerMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
    routerMock: {
        push: vi.fn(),
    },
}))

vi.mock('@/utils/axios', () => ({ default: axiosMock }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { slug: 'escuela-demo' } }),
    useRouter: () => routerMock,
}))

import useSuperAdminSchoolForm from '@/composables/admin/school/superAdminSchoolForm'

const Harness = defineComponent({
    setup() {
        return useSuperAdminSchoolForm('edit')
    },
    template: '<div />',
})

const CreateHarness = defineComponent({
    setup() {
        return useSuperAdminSchoolForm('create')
    },
    template: '<div />',
})

describe('useSuperAdminSchoolForm category format', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        axiosMock.post.mockReset()
        routerMock.push.mockReset()
        axiosMock.get.mockResolvedValue({
            data: {
                school: {
                    name: 'Escuela Demo',
                    category_format: 'sub_age',
                },
                schools: [],
                multiple_schools: [],
            },
        })
        axiosMock.post.mockResolvedValue({ data: { success: true } })
        vi.stubGlobal('Swal', { fire: vi.fn().mockResolvedValue({ isConfirmed: true }) })
        vi.stubGlobal('showMessage', vi.fn())
    })

    it('warns and submits category changes through the super-admin endpoint', async () => {
        const wrapper = mount(Harness)
        await nextTick()
        await nextTick()

        await wrapper.vm.submit({
            ...wrapper.vm.initialValues,
            category_format: 'birth_year',
        }, { setErrors: vi.fn() })

        expect(Swal.fire).toHaveBeenCalledWith(expect.objectContaining({
            title: '¿Cambiar el formato de categorías?',
            confirmButtonText: 'Sí, convertir',
        }))
        expect(axiosMock.post).toHaveBeenCalledWith(
            '/api/v2/admin/schools/escuela-demo',
            expect.any(FormData)
        )
        expect(axiosMock.post.mock.calls[0][1].get('category_format')).toBe('birth_year')
        wrapper.unmount()
    })

    it('does not submit when the category conversion is cancelled', async () => {
        Swal.fire.mockResolvedValue({ isConfirmed: false })
        const wrapper = mount(Harness)
        await nextTick()
        await nextTick()

        await wrapper.vm.submit({
            ...wrapper.vm.initialValues,
            category_format: 'birth_year',
        }, { setErrors: vi.fn() })

        expect(axiosMock.post).not.toHaveBeenCalled()
        wrapper.unmount()
    })

    it('submits the selected group pricing mode when creating a school', async () => {
        axiosMock.get.mockResolvedValue({ data: { schools: [] } })

        const wrapper = mount(CreateHarness)
        await nextTick()
        await nextTick()

        await wrapper.vm.submit({
            ...wrapper.vm.initialValues,
            name: 'Escuela Tarifas',
            address: 'Calle 123',
            phone: '3001234567',
            agent: 'Administradora',
            email: 'tarifas@example.com',
            is_enable: '1',
            training_group_monthly_payment_enabled: true,
        }, { setErrors: vi.fn() })

        expect(axiosMock.post).toHaveBeenCalledWith(
            '/api/v2/admin/schools',
            expect.any(FormData)
        )
        expect(axiosMock.post.mock.calls[0][1].get('training_group_monthly_payment_enabled')).toBe('1')
        wrapper.unmount()
    })
})
