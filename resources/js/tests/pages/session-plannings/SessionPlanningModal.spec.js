import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, settingsStore } = vi.hoisted(() => ({
    apiMock: { get: vi.fn(), post: vi.fn(), put: vi.fn() },
    settingsStore: {
        groups: [{ id: 10, name: 'Grupo A', full_group: 'Grupo A' }],
        getSettings: vi.fn().mockResolvedValue(undefined),
    },
}))

vi.mock('@/utils/axios', () => ({ default: apiMock }))
vi.mock('@/store/settings-store', () => ({ useSetting: () => settingsStore }))
vi.mock('@/pages/methodology/SoccerFieldDiagramEditor.vue', () => ({
    default: { name: 'SoccerFieldDiagramEditor', template: '<div class="field-editor-stub" />' },
}))

import SessionPlanningModal from '@/pages/session-plannings/SessionPlanningModal.vue'

function mountModal() {
    window.bootstrap = { Modal: vi.fn(function () { this.show = vi.fn(); this.hide = vi.fn() }) }
    window.Swal = { fire: vi.fn().mockResolvedValue({ isConfirmed: true }) }
    return mount(SessionPlanningModal, {
        props: { show: false, sessionId: null },
        global: {
            stubs: {
                Loader: true,
                CustomSelect2: {
                    props: ['modelValue', 'options'], emits: ['update:modelValue'],
                    template: '<button type="button" class="select-stub" />',
                },
                CustomMultiSelect: {
                    props: ['modelValue', 'options'], emits: ['update:modelValue'],
                    template: '<div class="multi-select-stub" />',
                },
            },
        },
    })
}

describe('SessionPlanningModal', () => {
    beforeEach(() => vi.clearAllMocks())

    it('creates between one and four diagram phases and confirms destructive reductions', async () => {
        const wrapper = mountModal()
        await wrapper.vm.$.setupState.changePhaseCount(4)
        expect(wrapper.vm.$.setupState.form.phases).toHaveLength(4)
        expect(wrapper.findAllComponents({ name: 'SoccerFieldDiagramEditor' })).toHaveLength(4)

        await wrapper.vm.$.setupState.changePhaseCount(1)
        await flushPromises()
        expect(window.Swal.fire).toHaveBeenCalledOnce()
        expect(wrapper.vm.$.setupState.form.phases).toHaveLength(1)
    })

    it('blocks the next step until the required general fields are complete', async () => {
        const wrapper = mountModal()
        const nextButton = wrapper.findAll('button').find(button => button.text() === 'Siguiente')
        await nextButton.trigger('click')
        expect(wrapper.text()).toContain('Completa grupo, periodo, sesión y día de entrenamiento.')
        expect(wrapper.vm.$.setupState.step).toBe(0)
    })

    it('requires a name before leaving each phase', async () => {
        const wrapper = mountModal()
        Object.assign(wrapper.vm.$.setupState.form, {
            training_group_id: '10', period: '1', session: '1', date: '2026-07-01',
        })
        wrapper.vm.$.setupState.step = 1
        await wrapper.vm.$.setupState.next()
        expect(wrapper.text()).toContain('El nombre de la fase 1 es obligatorio.')
        expect(wrapper.vm.$.setupState.step).toBe(1)
    })
})
