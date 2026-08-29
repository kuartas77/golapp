import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

const {
    apiMock,
    reloadTableMock,
    swalFireMock,
    modalHideMock,
    modalShowMock,
} = vi.hoisted(() => ({
    apiMock: { get: vi.fn(), post: vi.fn() },
    reloadTableMock: vi.fn(),
    swalFireMock: vi.fn().mockResolvedValue(undefined),
    modalHideMock: vi.fn(),
    modalShowMock: vi.fn(),
}))

vi.mock('@/utils/axios', () => ({ default: apiMock }))
vi.mock('@/composables/player/playersList', () => ({
    default: () => ({
        options: {},
        table: null,
        editPlayer: vi.fn(),
        showSummary: vi.fn(),
        reloadTable: reloadTableMock,
        globalError: null,
    }),
}))
vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => ({ start: vi.fn() }),
}))
vi.mock('@/store/auth-user', () => ({
    useAuthUser: () => ({
        hasRole: role => role === 'school',
        hasAnyRole: roles => roles.includes('school'),
    }),
}))

import PlayersList from '@/pages/players/PlayersList.vue'

const DatatableTemplateStub = defineComponent({
    template: '<div />',
})

function mountPage() {
    return mount(PlayersList, {
        global: {
            stubs: {
                panel: { template: '<section><slot name="header" /><slot name="body" /></section>' },
                breadcrumb: true,
                ContentState: true,
                DatatableTemplate: DatatableTemplateStub,
                PageTutorialOverlay: true,
            },
        },
    })
}

describe('PlayersList queued import', () => {
    beforeEach(() => {
        vi.useFakeTimers()
        apiMock.get.mockReset()
        apiMock.post.mockReset()
        reloadTableMock.mockReset()
        swalFireMock.mockClear()
        modalHideMock.mockReset()
        modalShowMock.mockReset()

        apiMock.get.mockImplementation(url => {
            if (url.endsWith('/latest')) {
                return Promise.resolve({ data: { import: null } })
            }

            return Promise.resolve({
                data: {
                    import: {
                        id: 'import-uuid',
                        status: 'completed',
                        filename: 'deportistas.xlsx',
                        summary: {
                            created_players: 200,
                            updated_players: 0,
                            created_inscriptions: 200,
                        },
                    },
                },
            })
        })
        apiMock.post.mockResolvedValue({
            data: {
                message: 'La importación quedó en cola y se procesará en segundo plano.',
                import: {
                    id: 'import-uuid',
                    status: 'pending',
                    filename: 'deportistas.xlsx',
                    summary: null,
                },
            },
        })

        vi.stubGlobal('Swal', { fire: swalFireMock })
        vi.stubGlobal('bootstrap', {
            Modal: class {
                show = modalShowMock
                hide = modalHideMock
                dispose = vi.fn()
            },
        })
    })

    afterEach(() => {
        vi.useRealTimers()
        vi.unstubAllGlobals()
    })

    it('returns immediately and reloads the table only after the queued job completes', async () => {
        const wrapper = mountPage()
        await vi.waitFor(() => expect(apiMock.get).toHaveBeenCalledWith(
            '/api/v2/import/players/latest',
            { skipGlobalLoader: true }
        ))

        const state = wrapper.vm.$.setupState
        state.importFile = new File(['players'], 'deportistas.xlsx')

        await state.submitImport()

        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/import/players', expect.any(FormData))
        expect(reloadTableMock).not.toHaveBeenCalled()
        expect(state.activeImport.status).toBe('pending')

        await vi.advanceTimersByTimeAsync(3000)
        await vi.waitFor(() => expect(reloadTableMock).toHaveBeenCalledOnce())

        expect(apiMock.get).toHaveBeenCalledWith(
            '/api/v2/import/players/import-uuid',
            { skipGlobalLoader: true }
        )
        expect(state.activeImport.status).toBe('completed')
        expect(swalFireMock).toHaveBeenLastCalledWith(expect.objectContaining({
            icon: 'success',
            title: 'Importación completada',
        }))

        wrapper.unmount()
    })
})
