import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, settingsStore } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
    },
    settingsStore: {
        groups: [{ id: 10, name: 'Grupo A', full_group: 'Grupo A' }],
        training_session_tasks: [{ value: '1', label: 'Técnica' }],
        training_session_general_objectives: [],
        training_session_specific_goals: [],
        training_session_contents: [],
        getSettings: vi.fn().mockResolvedValue(undefined),
    },
}))

vi.mock('@/utils/axios', () => ({ default: apiMock }))
vi.mock('@/store/settings-store', () => ({ useSetting: () => settingsStore }))

import TrainingSessionModal from '@/pages/training-sessions/TrainingSessionModal.vue'

const classDay = {
    id: '1061',
    index: 1,
    date: 1,
    day: 'Lunes',
    month: 6,
    month_name: 'Junio',
    year: 2026,
    group_id: 10,
}

function mountModal(props = { show: false, sessionId: null }) {
    window.bootstrap = {
        Modal: vi.fn(function () {
            this.show = vi.fn()
            this.hide = vi.fn()
        }),
    }
    vi.stubGlobal('showMessage', vi.fn())
    vi.stubGlobal('modalHidden', vi.fn())

    return mount(TrainingSessionModal, {
        props,
        global: {
            mocks: { $handleBackendErrors: vi.fn() },
            stubs: {
                Loader: true,
                Wizard: { template: '<div><slot name="info"/><slot/></div>' },
                Step: { template: '<section><slot/></section>' },
                flatPickr: { props: ['modelValue'], template: '<input />' },
                inputField: {
                    props: ['name', 'label', 'readonly'],
                    template: '<div><label>{{ label }}</label><input :name="name" :readonly="readonly" /></div>',
                },
                CustomSelect2: {
                    name: 'CustomSelect2',
                    props: ['id', 'modelValue', 'options', 'disabled'],
                    emits: ['update:modelValue'],
                    template: '<button type="button" class="select-stub" :data-id="id" :disabled="disabled" />',
                },
                CustomMultiSelect: {
                    props: ['id', 'modelValue', 'options'],
                    emits: ['update:modelValue'],
                    template: '<div class="multiselect-stub" :data-id="id">{{ options.map(option => option.label).join(", ") }}</div>',
                },
            },
        },
    })
}

function validValues(overrides = {}) {
    return {
        training_group_id: 10,
        month: 6,
        period: '1',
        session: '1',
        date: '2026-06-01',
        hour: '02:00 PM',
        training_ground: 'Cancha',
        material: '',
        warm_up: '',
        back_to_calm: '5',
        players: '1',
        absences: '',
        absence_inscription_ids: [{ value: 21, label: 'Jugador Ausente' }],
        incidents: '',
        feedback: '',
        tasks: [1, 2, 3].map((taskNumber) => ({
            task_number: taskNumber,
            task_name: taskNumber === 1 ? 'Técnica' : '',
            general_objective: '',
            specific_goal: '',
            content_one: '',
            content_two: '',
            content_three: '',
            ts: '',
            sr: '',
            tt: '',
            observations: '',
        })),
        ...overrides,
    }
}

describe('TrainingSessionModal attendance closure', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        apiMock.post.mockReset()
        apiMock.put.mockReset()
    })

    it('initializes the validated hour field with the visible default value', async () => {
        const wrapper = mountModal()
        await flushPromises()

        expect(wrapper.vm.$.setupState.form.values.hour).toBe('02:00 PM')
    })

    it('does not show required-field errors when selecting the training group', async () => {
        apiMock.get.mockResolvedValue({ data: [classDay] })
        const wrapper = mountModal()
        await flushPromises()
        expect(wrapper.vm.$.setupState.form.errors).toEqual({})

        const groupSelect = wrapper.findAllComponents({ name: 'CustomSelect2' })
            .find((component) => component.props('id') === 'training_group_id')
        groupSelect.vm.$emit('update:modelValue', 10)
        await flushPromises()

        expect(wrapper.vm.$.setupState.form.values.training_group_id).toBe(10)
        expect(wrapper.get('.modal-body').classes()).toContain('suppress-validation-errors')
    })

    it('transforms selected multiselect options into inscription ids', async () => {
        apiMock.post.mockResolvedValue({ data: { data: { id: 1 } } })
        const wrapper = mountModal()
        await flushPromises()

        await wrapper.vm.$.setupState.onSubmit(validValues(), { resetForm: vi.fn(), setErrors: vi.fn() })

        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/training-sessions', expect.objectContaining({
            sync_attendance: true,
            absence_inscription_ids: [21],
            players: '1',
        }))
    })

    it('loads players for the selected day and recalculates attending players', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                data: {
                    players: [
                        { value: 21, label: 'Jugador Ausente' },
                        { value: 22, label: 'Jugador Asistente' },
                    ],
                    protected_players: [{ value: 23, label: 'Jugador Excusado', status_label: 'Excusa' }],
                    current_absence_ids: [21],
                },
            },
        })
        const wrapper = mountModal()
        await flushPromises()

        await wrapper.vm.$.setupState.loadAttendanceContext(10, '2026-06-01')
        await flushPromises()

        expect(wrapper.text()).toContain('Jugador Ausente')
        expect(wrapper.text()).toContain('Jugador Excusado (Excusa)')
        expect(wrapper.text()).toContain('Izquierda: Asistieron')
        expect(wrapper.text()).toContain('Derecha: Faltaron')
        expect(wrapper.text()).toContain('Los deportistas que permanezcan en la izquierda se marcarán automáticamente como asistencia')
        expect(wrapper.vm.$.setupState.form.values.players).toBe(1)
    })

    it('loads automatic attendance for sessions that were not previously synchronized', async () => {
        apiMock.get.mockImplementation((url) => {
            if (url.includes('/training-sessions/99')) {
                return Promise.resolve({
                    data: {
                        data: {
                            id: 99,
                            training_group_id: 10,
                            date: '2026-06-01',
                            period: '1',
                            session: '1',
                            hour: '02:00 PM',
                            players: '15',
                            absences: 'Texto histórico',
                            attendance_synced: false,
                            tasks: [],
                        },
                    },
                })
            }

            if (url.includes('/training_group/classdays')) {
                return Promise.resolve({ data: [classDay] })
            }

            if (url.includes('/training-sessions/attendance-context')) {
                return Promise.resolve({
                    data: {
                        data: {
                            players: [{ value: 21, label: 'Jugador Ausente' }],
                            protected_players: [],
                            current_absence_ids: [21],
                        },
                    },
                })
            }

            return Promise.reject(new Error(`Unexpected URL ${url}`))
        })

        const wrapper = mountModal({ show: false, sessionId: 99 })
        await wrapper.setProps({ show: true })
        await flushPromises()

        expect(wrapper.text()).not.toContain('Activar registro automático de asistencia')
        expect(apiMock.get).toHaveBeenCalledWith(
            '/api/v2/training-sessions/attendance-context',
            { params: { training_group_id: 10, date: '2026-06-01' } }
        )
    })
})
