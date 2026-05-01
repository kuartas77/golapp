import { flushPromises, mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

const {
    apiMock,
    routeMock,
    routerPushMock,
    tutorialMock,
} = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
    },
    routeMock: {
        query: {},
    },
    routerPushMock: vi.fn(),
    tutorialMock: {
        start: vi.fn(),
    },
}))

vi.mock('@/utils/axios', () => ({
    default: apiMock,
}))

vi.mock('vue-router', () => ({
    useRoute: () => routeMock,
    useRouter: () => ({
        push: routerPushMock,
    }),
}))

vi.mock('@/composables/usePageTutorial', () => ({
    usePageTutorial: () => tutorialMock,
}))

vi.mock('@/composables/useBackofficeAccess', () => ({
    useBackofficeAccess: () => ({
        access: {
            reports: { value: true },
        },
    }),
}))

vi.mock('@/tutorials/dashboard', () => ({
    kpiTutorial: {
        steps: [],
    },
}))

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}))

import KpiIndex from '@/pages/kpi/Index.vue'

const wrappers = []

const createPayload = (overrides = {}) => ({
    filters: {
        years: [{ value: 2026, label: '2026' }],
        months: [{ value: 4, label: 'Abril' }, { value: 5, label: 'Mayo' }],
        selectedYear: 2026,
        selectedMonth: 4,
        selectedGroupId: 10,
    },
    group_options: [{ value: 10, label: 'Halcones' }],
    summary_cards: [
        {
            key: 'monthly_revenue',
            label: 'Recaudo mensualidades',
            value: 120000,
            format: 'currency',
            helper: 'Acumulado del año',
        },
        {
            key: 'attendance_percentage',
            label: '% asistencia del mes',
            value: 75,
            format: 'percentage',
            helper: 'Mes seleccionado',
        },
    ],
    payment_group_report: {
        categories: ['Halcones'],
        data: [{ name: 'Pagas', data: [2] }],
    },
    amount_payment_group_report: {
        categories: ['Halcones'],
        data: [
            { type: 'column', name: 'Mensualidades', data: [120000] },
            { type: 'line', name: '% de cumplimiento', data: [75] },
        ],
    },
    monthly_trend_report: {
        categories: ['Ene', 'Feb', 'Mar', 'Abr'],
        data: [{ type: 'column', name: 'Valor', data: [0, 0, 60000, 120000] }],
    },
    attendance_mix_report: {
        categories: ['Asistencias', 'Excusas'],
        data: [6, 2],
    },
    rankings: {
        compliance: [{ training_group_id: 10, label: 'Halcones', value: 75, format: 'percentage' }],
        low_attendance: [],
        debt: [],
        flagged: [],
    },
    report_links: {
        assists: '/informes/asistencias?year=2026&month=4&training_group_id=10',
        payments: '/informes/pagos?year=2026&training_group_id=10',
        attendance_payment: '/informes/mensualidades-asistencias?year=2026&month=4&training_group_id=10',
    },
    permissions: {
        can_view_monetary_values: true,
    },
    ...overrides,
})

const mountPage = async (payload = createPayload()) => {
    apiMock.get.mockResolvedValue({
        data: payload,
    })

    const wrapper = mount(KpiIndex, {
        global: {
            stubs: {
                PageTutorialOverlay: {
                    template: '<div />',
                },
                CustomSelect2: {
                    name: 'CustomSelect2',
                    props: ['id', 'modelValue', 'options', 'placeholder'],
                    template: '<select :id="id"></select>',
                },
                apexchart: {
                    props: ['options', 'series', 'type', 'height'],
                    template: '<div class="chart-stub"></div>',
                },
                'router-link': {
                    props: ['to'],
                    template: '<a :href="typeof to === \'string\' ? to : \'#\'"><slot /></a>',
                },
            },
        },
    })

    wrappers.push(wrapper)
    await flushPromises()
    await flushPromises()

    return wrapper
}

describe('KPI dashboard page', () => {
    beforeEach(() => {
        apiMock.get.mockReset()
        routeMock.query = {
            year: '2026',
            month: '4',
            training_group_id: '10',
        }
        routerPushMock.mockReset()
        tutorialMock.start.mockReset()
    })

    afterEach(() => {
        while (wrappers.length) {
            const wrapper = wrappers.pop()
            wrapper.unmount()
        }
    })

    it('hydrates from the query, renders KPI cards and keeps report links filtered', async () => {
        const wrapper = await mountPage()
        const state = wrapper.vm.$.setupState

        expect(apiMock.get).toHaveBeenCalledWith('/api/v2/kpis', expect.objectContaining({
            params: {
                year: '2026',
                month: '4',
                training_group_id: '10',
            },
            skipGlobalLoader: true,
        }))
        expect(state.filters.year).toBe(2026)
        expect(state.filters.month).toBe(4)
        expect(state.filters.training_group_id).toBe(10)
        expect(state.reportLinks.assists).toBe('/informes/asistencias?year=2026&month=4&training_group_id=10')
        expect(wrapper.text()).toContain('Indicadores del backoffice')
        expect(wrapper.text()).toContain('Recaudo mensualidades')
        expect(wrapper.text()).toContain('% asistencia del mes')
        expect(wrapper.findAll('.chart-stub')).toHaveLength(4)

        state.filters.month = 5
        state.filters.training_group_id = null

        await state.applyFilters()

        expect(routerPushMock).toHaveBeenCalledWith({
            name: 'kpi',
            query: {
                year: 2026,
                month: 5,
            },
        })
    })

    it('hides monetary KPI cards when the payload disables monetary access', async () => {
        const wrapper = await mountPage(createPayload({
            summary_cards: [
                {
                    key: 'payment_compliance',
                    label: '% cumplimiento global',
                    value: 75,
                    format: 'percentage',
                    helper: 'Acumulado del año',
                },
                {
                    key: 'attendance_percentage',
                    label: '% asistencia del mes',
                    value: 75,
                    format: 'percentage',
                    helper: 'Mes seleccionado',
                },
            ],
            amount_payment_group_report: {
                categories: ['Halcones'],
                data: [{ type: 'bar', name: '% de cumplimiento', data: [75] }],
                mode: 'compliance_only',
            },
            monthly_trend_report: {
                categories: ['Ene', 'Feb', 'Mar', 'Abr'],
                data: [{ type: 'line', name: 'Pagos', data: [0, 0, 1, 2] }],
                mode: 'payments_only',
            },
            report_links: {
                assists: '/informes/asistencias?year=2026&month=4&training_group_id=10',
                payments: null,
                attendance_payment: '/informes/mensualidades-asistencias?year=2026&month=4&training_group_id=10',
            },
            permissions: {
                can_view_monetary_values: false,
            },
        }))

        const state = wrapper.vm.$.setupState

        expect(state.canViewMonetaryValues).toBe(false)
        expect(wrapper.text()).toContain('% cumplimiento global')
        expect(wrapper.text()).not.toContain('Recaudo mensualidades')
        expect(wrapper.text()).not.toContain('Recaudo inscripciones')
    })
})
