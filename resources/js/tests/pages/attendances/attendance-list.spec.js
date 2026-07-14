import { flushPromises, mount } from '@vue/test-utils'
import { defineComponent, nextTick } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, settingsStore } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
    settingsStore: {
        groups: [
            { id: 10, name: 'Grupo A', full_group: 'Grupo A Sub 12' },
            { id: 11, name: 'Provisional', full_group: 'Provisional' },
        ],
        attendance_training_groups: [],
        getSettings: vi.fn().mockResolvedValue(undefined),
    },
}))

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}))

vi.mock('@/store/settings-store', () => ({
    useSetting: () => settingsStore,
}))

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}))

import useAttendances from '@/composables/attendances/attendances'

function mountComposable(dtMock = null) {
    const DataTableStub = defineComponent({
        name: 'DataTableStub',
        setup(_, { expose }) {
            expose({
                dt: dtMock,
            })

            return {}
        },
        template: '<div />',
    })

    const Harness = defineComponent({
        components: {
            DataTableStub,
        },
        setup() {
            return useAttendances()
        },
        template: `
            <div>
                <div id="composeModalObservation"></div>
                <DataTableStub ref="attendance_table" />
            </div>
        `,
    })

    return mount(Harness)
}

function activeRow(overrides = {}) {
    return {
        id: 1,
        assistance_one: null,
        inscription_deleted: false,
        inscription: {
            player: {
                full_names: 'Jugador Demo',
                unique_code: 'ABC123',
                category: 'Sub 12',
                photo_url: '/img/user.webp',
            },
        },
        ...overrides,
    }
}

describe('attendance-list composable', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        apiMock.post.mockReset()
        settingsStore.groups = [
            { id: 10, name: 'Grupo A', full_group: 'Grupo A Sub 12' },
            { id: 11, name: 'Provisional', full_group: 'Provisional' },
        ]
        settingsStore.attendance_training_groups = []
        settingsStore.getSettings.mockClear()
        vi.stubGlobal('showMessage', vi.fn())
        vi.stubGlobal('modalHidden', vi.fn())
        window.bootstrap = {
            Modal: vi.fn(function () {
                this.show = vi.fn()
                this.hide = vi.fn()
            }),
        }
    })

    it('filters players locally without redrawing the DataTable instance', async () => {
        const columnMock = vi.fn()
        const wrapper = mountComposable({ column: columnMock })

        await flushPromises()

        wrapper.vm.attendancesGroup = [
            activeRow({
                id: 1,
                inscription: {
                    player: {
                        full_names: 'Juan Perez',
                        unique_code: 'JP-1',
                        category: 'Sub 12',
                    },
                },
            }),
            activeRow({
                id: 2,
                inscription: {
                    player: {
                        full_names: 'Ana Gomez',
                        unique_code: 'AG-2',
                        category: 'Sub 10',
                    },
                },
            }),
        ]

        wrapper.vm.applyPlayerSearch({ target: { value: 'juan' } })

        expect(wrapper.vm.filteredAttendancesGroup).toHaveLength(1)
        expect(wrapper.vm.filteredAttendancesGroup[0].id).toBe(1)
        expect(columnMock).not.toHaveBeenCalled()
        expect(apiMock.get).not.toHaveBeenCalled()
    })

    it('uses attendance-specific groups so complementary groups can appear in the selector', async () => {
        settingsStore.attendance_training_groups = [
            { id: 20, name: 'Grupo Principal', full_group: 'Grupo Principal Sub 12', is_complementary: false },
            { id: 21, name: 'Porteros', full_group: 'Porteros', is_complementary: true },
        ]

        const wrapper = mountComposable()
        await flushPromises()

        expect(wrapper.vm.groups).toEqual([
            { value: 20, label: 'Grupo Principal Sub 12' },
            { value: 21, label: 'Porteros' },
        ])
    })

    it('creates missing attendance rows from the Vue flow and reloads the selected class day', async () => {
        const wrapper = mountComposable()
        await flushPromises()

        const classDay = {
            id: '211',
            group_id: 21,
            month: 1,
            year: 2026,
            column: 'assistance_one',
        }

        apiMock.get
            .mockResolvedValueOnce({ data: [classDay] })
            .mockResolvedValueOnce({ data: { rows: [], url_print: null, url_print_excel: null } })
            .mockResolvedValueOnce({ data: { rows: [activeRow({ id: 10 })], url_print: '/pdf', url_print_excel: '/excel' } })
        apiMock.post.mockResolvedValue({ data: [] })

        await wrapper.vm.handleSearchClassdays({ training_group_id: 21, month: 1 })
        await wrapper.vm.clickClassDay(classDay)
        await wrapper.vm.createMissingAttendances()

        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/assists', {
            training_group_id: 21,
            month: 1,
        })
        expect(apiMock.get).toHaveBeenLastCalledWith('/api/v2/assists', {
            params: {
                month: 1,
                training_group_id: 21,
                column: 'assistance_one',
                dataRaw: true,
            },
        })
        expect(wrapper.vm.attendancesGroup).toHaveLength(1)
        expect(showMessage).toHaveBeenCalledWith('Asistencias creadas correctamente.')
    })

    it('indexes player name, code and category for local DataTable search', async () => {
        const wrapper = mountComposable()
        await flushPromises()

        const searchText = wrapper.vm.options.columns[0].data(activeRow({
            inscription: {
                player: {
                    full_names: 'Ana Perez',
                    unique_code: 'AP-10',
                    category: 'Sub 10',
                },
            },
        }))

        expect(searchText).toBe('Ana Perez AP-10 Sub 10')
    })

    it('renders the player search input from the DataTable column title', async () => {
        const wrapper = mountComposable()
        await flushPromises()

        expect(wrapper.vm.options.columns[0].title).toContain('data-attendance-player-search="true"')
        expect(wrapper.vm.options.columns[0].title).toContain('Buscar deportista')
    })

    it('treats string zero retired flags as editable active rows', async () => {
        const wrapper = mountComposable()
        await flushPromises()

        expect(wrapper.vm.attendanceRowReadOnly(activeRow({ inscription_deleted: '0' }))).toBe(false)
        expect(wrapper.vm.attendanceRowReadOnly(activeRow({ inscription_deleted: 'false' }))).toBe(false)
        expect(wrapper.vm.attendanceRowReadOnly(activeRow({ inscription_deleted: '1' }))).toBe(true)
    })

    it('marks attendance for all loaded active rows and skips retired rows', async () => {
        const wrapper = mountComposable()
        await flushPromises()

        wrapper.vm.classDaySelected = {
            column: 'assistance_one',
            group_id: 10,
            month: 1,
            year: 2026,
        }
        wrapper.vm.attendancesGroup = [
            activeRow({ id: 1, assistance_one: 2 }),
            activeRow({ id: 2, assistance_one: null }),
            activeRow({ id: 3, assistance_one: null, inscription_deleted: true }),
        ]
        apiMock.post.mockResolvedValue({
            data: {
                data: {
                    requested_count: 2,
                    updated_count: 2,
                    skipped_count: 0,
                    updated_ids: [1, 2],
                },
            },
        })

        await nextTick()
        await wrapper.vm.markAttendanceForAllLoaded()

        expect(apiMock.post).toHaveBeenCalledTimes(1)
        expect(apiMock.post).toHaveBeenCalledWith('/api/v2/assists/bulk-update', {
            assist_ids: [1, 2],
            training_group_id: 10,
            month: 1,
            year: 2026,
            column: 'assistance_one',
            value: 1,
        })
        expect(wrapper.vm.attendancesGroup[0].assistance_one).toBe(1)
        expect(wrapper.vm.attendancesGroup[1].assistance_one).toBe(1)
        expect(wrapper.vm.attendancesGroup[2].assistance_one).toBeNull()
        expect(showMessage).toHaveBeenCalledWith('Asistencia marcada para 2 deportista(s).')
    })

    it('rolls back only rows that fail during bulk attendance marking', async () => {
        const wrapper = mountComposable()
        await flushPromises()

        wrapper.vm.classDaySelected = {
            column: 'assistance_one',
            group_id: 10,
            month: 1,
            year: 2026,
        }
        wrapper.vm.attendancesGroup = [
            activeRow({ id: 1, assistance_one: 2 }),
            activeRow({ id: 2, assistance_one: 3 }),
        ]
        apiMock.post.mockResolvedValue({
            data: {
                data: {
                    requested_count: 2,
                    updated_count: 1,
                    skipped_count: 1,
                    updated_ids: [1],
                },
            },
        })

        await nextTick()
        await wrapper.vm.markAttendanceForAllLoaded()

        expect(apiMock.post).toHaveBeenCalledTimes(1)
        expect(wrapper.vm.attendancesGroup[0].assistance_one).toBe(1)
        expect(wrapper.vm.attendancesGroup[1].assistance_one).toBe(3)
        expect(showMessage).toHaveBeenCalledWith(
            'Se marcaron 1 asistencia(s). 1 registro(s) no se pudieron guardar.',
            'warning'
        )
    })
})
