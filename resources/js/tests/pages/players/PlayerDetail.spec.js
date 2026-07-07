import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, swalFireMock, tutorialStartMock } = vi.hoisted(() => ({
    apiMock: { get: vi.fn() },
    swalFireMock: vi.fn().mockResolvedValue(undefined),
    tutorialStartMock: vi.fn(),
}))

vi.mock('@/utils/axios', () => ({ default: apiMock }))
vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { unique_code: 'PLAYER-100' } }),
    useRouter: () => ({ push: vi.fn() }),
}))
vi.mock('@/composables/player/playerDetail', () => ({
    default: () => ({
        globalError: null,
        onSubmit: vi.fn(),
        wizardOptions: () => ({}),
        currentTextPlayer: 'Laura Gómez',
        step: 0,
        initialValues: {},
        flatpickrConfig: {},
        settings: {
            documentTypeOptions: [],
            genderOptions: [],
            bloodTypeOptions: [],
            jornadaOptions: [],
        },
        schema: {},
        degrees: [],
        loadingText: '',
        isLoading: false,
        guardianPortalEnabled: false,
        formErrorSummary: [],
        hasGeneralErrors: false,
        goToStep: vi.fn(),
    }),
}))
vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => ({ start: tutorialStartMock }),
}))

import PlayerDetail from '@/pages/players/PlayerDetail.vue'

const FormStub = defineComponent({
    template: '<form><slot :validate="async () => ({ valid: true })" :handleSubmit="fn => fn" /></form>',
})

const WizardStub = defineComponent({
    template: '<div><slot name="info" /></div>',
})

function mountPage() {
    return mount(PlayerDetail, {
        global: {
            config: {
                globalProperties: {
                    $swal: { fire: swalFireMock },
                },
            },
            stubs: {
                panel: { template: '<section><slot name="body" /></section>' },
                breadcrumb: true,
                Form: FormStub,
                Wizard: WizardStub,
                Step: true,
                Loader: true,
                PageTutorialOverlay: true,
                AttendanceQrModal: true,
                flatPickr: true,
                inputField: true,
                inputFileImage: true,
                Field: true,
            },
        },
    })
}

describe('PlayerDetail financial clearance', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        swalFireMock.mockClear()
        vi.stubGlobal('open', vi.fn())
    })

    it('opens the PDF when the player has no overdue debts', async () => {
        apiMock.get.mockResolvedValue({ data: { eligible: true, debts: [], total_debt: 0 } })
        const wrapper = mountPage()

        await wrapper.get('button.btn-success').trigger('click')
        await vi.waitFor(() => expect(apiMock.get).toHaveBeenCalled())

        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/players/PLAYER-100/financial-clearance')
        expect(window.open).toHaveBeenCalledWith(
            '/api/v2/players/PLAYER-100/financial-clearance/pdf',
            '_blank',
            'noopener'
        )
        expect(swalFireMock).not.toHaveBeenCalled()
    })

    it('shows itemized debts and does not open the PDF', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                eligible: false,
                total_debt: 75000,
                debts: [{ year: 2025, label: 'Mensualidad enero', amount: 75000 }],
            },
        })
        const wrapper = mountPage()

        await wrapper.get('button.btn-success').trigger('click')
        await vi.waitFor(() => expect(swalFireMock).toHaveBeenCalled())

        expect(window.open).not.toHaveBeenCalled()
        expect(swalFireMock.mock.calls[0][0].title).toContain('No es posible')
        expect(swalFireMock.mock.calls[0][0].html).toContain('Mensualidad enero')
        expect(swalFireMock.mock.calls[0][0].html).toContain('2025')
    })
})
