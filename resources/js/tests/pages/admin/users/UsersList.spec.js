import { mount, flushPromises } from '@vue/test-utils'
import { defineComponent } from 'vue'
import { describe, expect, it, vi, beforeEach } from 'vitest'

const { axiosMock, modalShowMock, modalHideMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
    modalShowMock: vi.fn(),
    modalHideMock: vi.fn(),
}))

vi.mock('@/utils/axios', () => ({
    default: axiosMock,
}))

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}))

vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => ({ start: vi.fn() }),
}))

vi.mock('@/tutorials/admin', () => ({
    usersListTutorial: [],
}))

import UsersList from '@/pages/admin/users/UsersList.vue'

const DatatableTemplateStub = defineComponent({
    template: '<div><slot name="actions" :cellData="21" /></div>',
})

const FormStub = defineComponent({
    template: '<form><slot /></form>',
    setup(props, { expose }) {
        expose({
            resetForm: vi.fn(),
            setValues: vi.fn(),
        })
    },
})

function mountPage() {
    window.bootstrap = {
        Modal: vi.fn(class {
            show = modalShowMock
            hide = modalHideMock
        }),
    }

    return mount(UsersList, {
        global: {
            stubs: {
                panel: { template: '<section><slot name="header" /><slot name="body" /></section>' },
                breadcrumb: { template: '<div />' },
                PageTutorialOverlay: { template: '<div />' },
                DatatableTemplate: DatatableTemplateStub,
                Form: FormStub,
                Field: { template: '<div><slot :field="{}" :handleChange="() => {}" :handleBlur="() => {}" /></div>' },
                ErrorMessage: { template: '<div />' },
                inputField: { template: '<input />' },
            },
        },
    })
}

describe('UsersList profile modal', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        modalShowMock.mockReset()
        modalHideMock.mockReset()
    })

    it('opens a readonly profile modal from the users table action', async () => {
        axiosMock.get.mockResolvedValue({
            data: {
                data: {
                    user: {
                        id: 21,
                        name: 'Instructor Uno',
                        email: 'instructor@example.com',
                    },
                    profile: {
                        identification_document: '123',
                        date_birth: '1992-01-20',
                        gender: 'M',
                        position: 'ENTRENADOR(A)',
                        address: 'Calle 1',
                        phone: '601',
                        mobile: '300',
                        studies: 'Estudios',
                        references: 'Referencias',
                        contacts: 'Contactos',
                        experience: 'Experiencia',
                        aptitude: 'Aptitudes',
                    },
                    gender_options: [{ value: 'M', label: 'Masculino' }],
                    position_options: [{ value: 'ENTRENADOR(A)', label: 'ENTRENADOR(A)' }],
                    can_update: false,
                },
            },
        })

        const wrapper = mountPage()
        await wrapper.find('button[title="Ver perfil"]').trigger('click')
        await flushPromises()

        expect(axiosMock.get).toHaveBeenCalledWith('/api/v2/admin/users/21/profile')
        expect(modalShowMock).toHaveBeenCalled()
        expect(wrapper.text()).toContain('Instructor Uno')
        expect(wrapper.text()).toContain('Masculino')
        expect(wrapper.text()).toContain('Experiencia')
        expect(wrapper.text()).not.toContain('Guardar perfil')
        wrapper.unmount()
    })
})
