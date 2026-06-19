import { mount, flushPromises } from '@vue/test-utils'
import { describe, expect, it, vi, beforeEach } from 'vitest'

const { axiosMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        put: vi.fn(),
    },
}))

vi.mock('@/utils/axios', () => ({
    default: axiosMock,
}))

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}))

import UserProfile from '@/pages/profile/UserProfile.vue'

const profilePayload = {
    user: {
        id: 10,
        name: 'Carlos Instructor',
        email: 'carlos@example.com',
    },
    profile: {
        id: 5,
        date_birth: '1990-05-10',
        identification_document: '123456',
        gender: 'M',
        address: 'Calle 10',
        phone: '6011234567',
        mobile: '3001234567',
        studies: 'Licenciatura',
        references: 'Referencia',
        contacts: 'Contacto',
        experience: 'Experiencia',
        position: 'ENTRENADOR(A)',
        aptitude: 'Liderazgo',
    },
    can_update: true,
    gender_options: [
        { value: 'M', label: 'Masculino' },
        { value: 'F', label: 'Femenino' },
    ],
    position_options: [
        { value: 'ENTRENADOR(A)', label: 'ENTRENADOR(A)' },
        { value: 'COORDINADOR(A)', label: 'COORDINADOR(A)' },
    ],
}

function mountPage() {
    return mount(UserProfile, {
        global: {
            stubs: {
                Loader: { template: '<div />' },
                breadcrumb: { template: '<div />' },
            },
        },
    })
}

describe('UserProfile', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        axiosMock.put.mockReset()
        axiosMock.get.mockResolvedValue({ data: { data: profilePayload } })
    })

    it('loads the authenticated profile into the form', async () => {
        const wrapper = mountPage()
        await flushPromises()

        expect(axiosMock.get).toHaveBeenCalledWith('/api/v2/profile')
        expect(wrapper.find('#profile-document').element.value).toBe('123456')
        expect(wrapper.find('#profile-position').element.value).toBe('ENTRENADOR(A)')
        expect(wrapper.text()).toContain('Carlos Instructor')
        wrapper.unmount()
    })

    it('saves only profile fields', async () => {
        axiosMock.put.mockResolvedValue({
            data: {
                message: 'Perfil actualizado correctamente.',
                data: profilePayload,
            },
        })

        const wrapper = mountPage()
        await flushPromises()

        await wrapper.find('#profile-document').setValue('987654')
        await wrapper.find('#profile-mobile').setValue('3112223344')
        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()

        expect(axiosMock.put).toHaveBeenCalledWith('/api/v2/profile', expect.objectContaining({
            identification_document: '987654',
            mobile: '3112223344',
            position: 'ENTRENADOR(A)',
        }))
        expect(axiosMock.put.mock.calls[0][1]).not.toHaveProperty('name')
        expect(axiosMock.put.mock.calls[0][1]).not.toHaveProperty('email')
        expect(wrapper.text()).toContain('Perfil actualizado correctamente.')
        wrapper.unmount()
    })

    it('shows backend validation errors', async () => {
        axiosMock.put.mockRejectedValue({
            response: {
                data: {
                    message: 'Los datos enviados no son válidos.',
                    errors: {
                        identification_document: ['El documento es demasiado largo.'],
                    },
                },
            },
        })

        const wrapper = mountPage()
        await flushPromises()

        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()

        expect(wrapper.text()).toContain('El documento es demasiado largo.')
        wrapper.unmount()
    })
})
