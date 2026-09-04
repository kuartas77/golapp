import { mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

const { authState, modalDisposeMock } = vi.hoisted(() => ({
    authState: {
        roles: [],
    },
    modalDisposeMock: vi.fn(),
}))

vi.mock('@/store/auth-user', () => ({
    useAuthUser: () => ({
        hasRole: role => authState.roles.includes(role),
        hasAnyRole: roles => roles.some(role => authState.roles.includes(role)),
    }),
}))

vi.mock('@/composables/invoices/inscriptionCustomChargesList', () => ({
    default: () => ({
        options: {},
        reloadTable: vi.fn(),
        globalError: '',
    }),
}))

vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => ({ start: vi.fn() }),
}))

vi.mock('@/tutorials/invoices', () => ({ customChargesTutorial: [] }))

import InscriptionCustomCharges from '@/pages/invoices/InscriptionCustomCharges.vue'

const mountPage = () => {
    window.bootstrap = {
        Modal: vi.fn(class {
            dispose = modalDisposeMock
        }),
    }
    vi.stubGlobal('Swal', { fire: vi.fn() })
    vi.stubGlobal('showMessage', vi.fn())

    return mount(InscriptionCustomCharges, {
        global: {
            stubs: {
                PageTutorialOverlay: { template: '<div />' },
                CurrencyInput: { template: '<input />' },
                'flat-pickr': { template: '<input />' },
                DatatableTemplate: {
                    template: `
                        <div>
                            <slot
                                name="actions"
                                :row-data="{ id: 7, name: 'Transporte', status: 'pending' }"
                            />
                        </div>
                    `,
                },
            },
        },
    })
}

describe('InscriptionCustomCharges access', () => {
    beforeEach(() => {
        authState.roles = []
        modalDisposeMock.mockReset()
    })

    afterEach(() => {
        vi.unstubAllGlobals()
    })

    it('shows the list as read only for assistants', () => {
        authState.roles = ['assistant']
        const wrapper = mountPage()
        const buttonLabels = wrapper.findAll('button').map(button => button.text().trim())

        expect(wrapper.get('h1').text()).toBe('Cargos personalizados')
        expect(buttonLabels).not.toContain('Editar')
        expect(buttonLabels).not.toContain('Eliminar')

        wrapper.unmount()
    })

    it('keeps edit actions available for school administrators', () => {
        authState.roles = ['school']
        const wrapper = mountPage()

        expect(wrapper.text()).toContain('Editar')
        expect(wrapper.text()).toContain('Eliminar')

        wrapper.unmount()
    })
})
