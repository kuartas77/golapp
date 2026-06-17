import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import { describe, expect, it, vi } from 'vitest'

const { axiosMock, routerPushMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
    },
    routerPushMock: vi.fn(),
}))

vi.mock('@/utils/axios', () => ({
    default: axiosMock,
}))

vi.mock('vue-router', () => ({
    useRoute: () => ({ params: { id: 1 } }),
    useRouter: () => ({ push: routerPushMock }),
}))

import SchoolOutingsIndex from '@/pages/school-outings/Index.vue'
import SchoolOutingShow from '@/pages/school-outings/Show.vue'

const PanelStub = { template: '<section><slot name="body" /></section>' }
const CurrencyInputStub = {
    props: ['modelValue'],
    emits: ['update:modelValue'],
    template: '<input :value="modelValue" @input="$emit(`update:modelValue`, Number($event.target.value))" />',
}
const CustomSelect2Stub = {
    props: ['modelValue', 'options', 'placeholder', 'disabled'],
    emits: ['update:modelValue', 'change'],
    template: '<select :value="modelValue" :disabled="disabled" @change="$emit(`update:modelValue`, $event.target.value); $emit(`change`, $event.target.value)"><option value="">{{ placeholder }}</option><option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option></select>',
}
const RouterLinkStub = defineComponent({
    props: ['to'],
    template: '<a><slot /></a>',
})
const DatatableTemplateStub = defineComponent({
    name: 'DatatableTemplate',
    props: ['id', 'options', 'data'],
    template: `
        <table :id="id">
            <tbody>
                <tr v-for="row in data" :key="row.id">
                    <td>{{ row.name }}</td>
                    <td>{{ row.target_total }}</td>
                    <td>{{ row.raised_total }}</td>
                    <td>{{ row.pending_total }}</td>
                    <td><slot name="actions" :row-data="row" /></td>
                </tr>
            </tbody>
        </table>
    `,
})

function mountWithGlobals(component) {
    vi.stubGlobal('moneyFormat', (value) => `$${Number(value || 0)}`)
    vi.stubGlobal('showMessage', vi.fn())

    return mount(component, {
        global: {
            stubs: {
                panel: PanelStub,
                CurrencyInput: CurrencyInputStub,
                CustomSelect2: CustomSelect2Stub,
                RouterLink: RouterLinkStub,
                DatatableTemplate: DatatableTemplateStub,
            },
        },
    })
}

describe('SchoolOutings', () => {
    beforeEach(() => {
        Object.values(axiosMock).forEach((mock) => mock.mockReset())
        routerPushMock.mockReset()
    })

    it('renders outing totals in the index', async () => {
        axiosMock.get.mockResolvedValueOnce({
            data: {
                data: [
                    {
                        id: 1,
                        name: 'Campeonato en Medellin',
                        departure_date: '2026-07-20',
                        participants_count_value: 2,
                        target_total: 300000,
                        raised_total: 120000,
                        pending_total: 180000,
                        status: 'open',
                        status_label: 'Abierta',
                        is_locked: false,
                    },
                ],
            },
        })

        const wrapper = mountWithGlobals(SchoolOutingsIndex)
        await vi.waitFor(() => expect(wrapper.text()).toContain('Campeonato en Medellin'))

        const datatable = wrapper.findComponent(DatatableTemplateStub)
        expect(datatable.exists()).toBe(true)
        expect(datatable.props('id')).toBe('school_outings_table')
        expect(datatable.props('options').columns).toHaveLength(8)
        expect(wrapper.text()).toContain('Campeonato en Medellin')
        expect(datatable.props('data')).toHaveLength(1)
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('renders detail summary and allows opening contribution form', async () => {
        axiosMock.get
            .mockResolvedValueOnce({
                data: {
                    all_t_groups: [],
                    categories: [],
                },
            })
            .mockResolvedValueOnce({
                data: {
                    data: {
                        id: 1,
                        name: 'Salida Armenia',
                        departure_date: '2026-08-01',
                        status: 'open',
                        status_label: 'Abierta',
                        is_locked: false,
                        participants_count_value: 1,
                        target_total: 100000,
                        raised_total: 25000,
                        pending_total: 75000,
                        progress_percent: 25,
                        activities: [{ id: 9, name: 'Pago directo', is_default: true }],
                        participants: [
                            {
                                id: 5,
                                target_amount: '100000.00',
                                raised_total: 25000,
                                pending_total: 75000,
                                status_label: 'Pendiente',
                                player: { full_names: 'Ana Perez', unique_code: 'SAL-001' },
                                inscription: { category: 'SUB-11', training_group: { name: 'Provisional' } },
                            },
                        ],
                        contributions: [],
                    },
                },
            })
            .mockResolvedValueOnce({ data: { data: [] } })

        const wrapper = mountWithGlobals(SchoolOutingShow)
        await vi.waitFor(() => expect(wrapper.text()).toContain('Salida Armenia'))

        expect(wrapper.text()).toContain('$100000')
        expect(wrapper.text()).toContain('$25000')
        expect(wrapper.text()).toContain('$75000')

        await wrapper.findAll('button').find((button) => button.text() === 'Abono').trigger('click')

        expect(wrapper.text()).toContain('Registrar abono')
        expect(wrapper.text()).toContain('Ana Perez')
        wrapper.unmount()
        vi.unstubAllGlobals()
    })
})
