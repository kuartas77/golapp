import { flushPromises, mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

const { apiMock, routerPushMock, routeState, settingsStore } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
    },
    routerPushMock: vi.fn(),
    routeState: {
        params: {
            id: '1',
        },
    },
    settingsStore: {
        paymentTypeOptions: [
            { value: 0, label: 'Pendiente' },
            { value: 1, label: 'Pagó' },
            { value: 2, label: 'Debe' },
        ],
        paymentTypeLabels: {
            0: 'Pendiente',
            1: 'Pagó',
            2: 'Debe',
        },
        getSettings: vi.fn().mockResolvedValue(undefined),
    },
}))

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}))

vi.mock('vue-router', () => ({
    useRoute: () => routeState,
    useRouter: () => ({
        push: routerPushMock,
    }),
}))

vi.mock('@/store/settings-store', () => ({
    useSetting: () => settingsStore,
}))

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}))

import InscriptionSummary from '@/pages/inscriptions/InscriptionSummary.vue'

const wrappers = []

function summaryPayload(overrides = {}) {
    return {
        data: {
            can_edit: true,
            current_year: 2026,
            inscription: {
                id: 1,
                unique_code: 'ABC123',
                year: 2026,
                category: 'Sub 10',
                status: 'active',
                status_label: 'Activa',
                start_date: '2026-01-10',
                pre_inscription: false,
                brother_payment: false,
                documents: {
                    photos: true,
                    copy_identification_document: false,
                    eps_certificate: true,
                    medic_certificate: false,
                    study_certificate: false,
                },
                training_group: {
                    id: 5,
                    name: 'Grupo A',
                    full_group: 'Grupo A · Sub 10',
                },
                stats: {},
            },
            player: {
                id: 9,
                unique_code: 'ABC123',
                full_names: 'Jugador Demo',
                photo_url: '/img/user.webp',
                identification_document: '1000',
                gender: 'M',
                date_birth: '2014-01-01',
            },
            years: [
                { id: 1, year: 2026, current: true, status_label: 'Activa' },
                { id: 2, year: 2025, current: false, status_label: 'Histórica' },
            ],
            payments: [
                {
                    id: 10,
                    year: 2026,
                    enrollment: 2,
                    enrollment_amount: 70000,
                    january: 2,
                    january_amount: 50000,
                },
            ],
            attendance: [
                {
                    id: 20,
                    year: 2026,
                    month: 1,
                    month_label: 'Enero',
                    registers: [
                        {
                            column: 'assistance_one',
                            class_number: 1,
                            day: 'lunes',
                            date: '2026-01-05',
                            attendance_date: '2026-01-05',
                            value: 1,
                            label: 'Asistencia',
                            observation: '',
                        },
                    ],
                },
                {
                    id: 21,
                    year: 2026,
                    month: 2,
                    month_label: 'Febrero',
                    registers: [
                        {
                            column: 'assistance_one',
                            class_number: 1,
                            day: 'lunes',
                            date: '2026-02-02',
                            attendance_date: '2026-02-02',
                            value: 2,
                            label: 'Falta',
                            observation: 'No asistió',
                        },
                    ],
                },
            ],
            invoices: [],
            evaluations: [],
            links: {
                print: '/export/inscription/9/1',
                stats: '/player/9/detail',
            },
            amounts: {
                enrollment: 70000,
                monthly: 50000,
                annuity: 480000,
            },
            ...overrides,
        },
    }
}

async function mountPage(payload = summaryPayload()) {
    apiMock.get.mockResolvedValue({ data: payload })

    const wrapper = mount(InscriptionSummary, {
        global: {
            stubs: {
                panel: {
                    template: '<section><slot name="body" /></section>',
                },
                breadcrumb: {
                    template: '<div />',
                },
                Loader: {
                    template: '<div />',
                },
                CurrencyInput: {
                    props: ['modelValue'],
                    emits: ['update:modelValue'],
                    template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', Number($event.target.value))" />',
                },
                RouterLink: {
                    props: ['to'],
                    template: '<a><slot /></a>',
                },
            },
        },
    })

    wrappers.push(wrapper)
    await flushPromises()
    await flushPromises()

    return wrapper
}

describe('InscriptionSummary', () => {
    beforeEach(() => {
        routeState.params.id = '1'
        apiMock.get.mockReset()
        apiMock.post.mockReset()
        routerPushMock.mockReset()
        settingsStore.getSettings.mockClear()
        vi.stubGlobal('moneyFormat', (value) => `$${value}`)
        vi.stubGlobal('showMessage', vi.fn())
    })

    afterEach(() => {
        while (wrappers.length) {
            wrappers.pop().unmount()
        }
        vi.unstubAllGlobals()
    })

    it('renders the inscription summary header', async () => {
        const wrapper = await mountPage()

        expect(wrapper.text()).toContain('Jugador Demo')
        expect(wrapper.text()).toContain('ABC123')
        expect(wrapper.text()).toContain('Editable')
        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/inscriptions/1/summary')
    })

    it('shows inline payment and attendance actions for current year inscriptions', async () => {
        const wrapper = await mountPage()

        await wrapper.findAll('button').find((button) => button.text() === 'Pagos').trigger('click')
        expect(wrapper.find('.fa-edit').exists()).toBe(true)

        await wrapper.findAll('button').find((button) => button.text() === 'Asistencias').trigger('click')
        expect(wrapper.text()).toContain('Guardar')
    })

    it('filters attendance by selected month in the frontend', async () => {
        const wrapper = await mountPage()
        const visibleAttendanceTitles = () => wrapper.findAll('.card h6').map((heading) => heading.text())

        await wrapper.findAll('button').find((button) => button.text() === 'Asistencias').trigger('click')
        expect(visibleAttendanceTitles()).toContain('Enero 2026')
        expect(visibleAttendanceTitles()).not.toContain('Febrero 2026')

        await wrapper.find('#attendance_month').setValue('2')
        expect(visibleAttendanceTitles()).toContain('Febrero 2026')
        expect(wrapper.find('textarea').element.value).toBe('No asistió')
        expect(visibleAttendanceTitles()).not.toContain('Enero 2026')
    })

    it('hides editing actions for previous year inscriptions', async () => {
        const wrapper = await mountPage(summaryPayload({
            can_edit: false,
            inscription: {
                ...summaryPayload().data.inscription,
                year: 2025,
            },
        }))

        expect(wrapper.text()).toContain('Sólo lectura')

        await wrapper.findAll('button').find((button) => button.text() === 'Pagos').trigger('click')
        expect(wrapper.text()).not.toContain('Editar')
    })

    it('navigates between inscription years', async () => {
        const wrapper = await mountPage()
        const yearSelect = wrapper.find('.summary-year-select')

        await yearSelect.setValue('2')

        expect(routerPushMock).toHaveBeenCalledWith({
            name: 'inscriptions.summary',
            params: { id: '2' },
        })
    })
})
