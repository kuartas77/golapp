import { mount } from '@vue/test-utils'
import { defineComponent, nextTick } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const {
    axiosMock,
    productClearPipelineMock,
    movementClearPipelineMock,
    productReloadMock,
    movementReloadMock,
} = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
    },
    productClearPipelineMock: vi.fn(),
    movementClearPipelineMock: vi.fn(),
    productReloadMock: vi.fn(),
    movementReloadMock: vi.fn(),
}))

vi.mock('@/utils/axios', () => ({
    default: axiosMock,
}))

vi.mock('@/composables/use-meta', () => ({
    usePageTitle: vi.fn(),
}))

import InventoryIndex from '@/pages/inventory/InventoryIndex.vue'

const DatatableTemplateStub = defineComponent({
    props: ['id', 'options'],
    setup(props, { expose }) {
        expose({
            table: {
                dt: {
                    clearPipeline: props.id === 'inventory_movements_table'
                        ? movementClearPipelineMock
                        : productClearPipelineMock,
                    ajax: {
                        reload: props.id === 'inventory_movements_table'
                            ? movementReloadMock
                            : productReloadMock,
                    },
                },
            },
        })

        return {}
    },
    template: '<table />',
})

function mountPage() {
    vi.stubGlobal('moneyFormat', (value) => `$${value}`)
    vi.stubGlobal('showMessage', vi.fn())

    return mount(InventoryIndex, {
        global: {
            stubs: {
                panel: { template: '<section><slot name="body" /></section>' },
                breadcrumb: { template: '<div />' },
                DatatableTemplate: DatatableTemplateStub,
                CurrencyInput: {
                    props: ['modelValue'],
                    emits: ['update:modelValue'],
                    template: '<input :value="modelValue" @input="$emit(`update:modelValue`, Number($event.target.value))" />',
                },
            },
        },
    })
}

describe('InventoryIndex', () => {
    beforeEach(() => {
        axiosMock.get.mockReset()
        axiosMock.post.mockReset()
        axiosMock.put.mockReset()
        productClearPipelineMock.mockReset()
        movementClearPipelineMock.mockReset()
        productReloadMock.mockReset()
        movementReloadMock.mockReset()
    })

    it('validates movement quantity before posting', async () => {
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState

        state.movementForm.type = 'exit'
        state.movementForm.quantity = 0
        state.movementForm.movement_date = '2026-06-05'

        await state.saveMovement()

        expect(axiosMock.post).not.toHaveBeenCalled()
        expect(state.formErrors.quantity).toBe('La cantidad debe ser mayor a cero.')
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('reloads the movement table after creating a product', async () => {
        axiosMock.post.mockResolvedValue({ data: { data: { id: 10 } } })
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState

        state.productForm.name = 'Camiseta'
        state.productForm.entry_price = 35000
        state.productForm.unit_price = 75000
        state.productForm.stock_quantity = 10
        state.productForm.minimum_stock = 2
        state.productForm.is_active = true

        await state.saveProduct()

        expect(axiosMock.post).toHaveBeenCalledWith('/api/v2/inventory/products', expect.objectContaining({
            name: 'Camiseta',
            entry_price: 35000,
            unit_price: 75000,
            stock_quantity: 10,
        }))
        expect(productClearPipelineMock).toHaveBeenCalledOnce()
        expect(movementClearPipelineMock).toHaveBeenCalledOnce()
        expect(productReloadMock).toHaveBeenCalledWith(null, false)
        expect(movementReloadMock).toHaveBeenCalledWith(null, false)
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('clears both table pipelines after recording a movement', async () => {
        axiosMock.get.mockResolvedValue({
            data: {
                data: {
                    id: 8,
                    name: 'Camiseta',
                    entry_price: '35000.00',
                    unit_price: '60000.00',
                    stock_quantity: 3,
                    minimum_stock: 1,
                },
            },
        })
        axiosMock.post.mockResolvedValue({ data: { data: { id: 25 } } })
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState

        await state.openMovementForm(8)
        state.movementForm.type = 'exit'
        state.movementForm.quantity = 1
        state.movementForm.movement_date = '2026-06-05'
        await state.saveMovement()

        expect(productClearPipelineMock).toHaveBeenCalledOnce()
        expect(movementClearPipelineMock).toHaveBeenCalledOnce()
        expect(productReloadMock).toHaveBeenCalledWith(null, false)
        expect(movementReloadMock).toHaveBeenCalledWith(null, false)
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('reloads movement table when opening the movement tab', () => {
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState

        state.setActiveTab('movements')

        expect(movementReloadMock).toHaveBeenCalledWith(null, false)
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('opens the edit modal with product data', async () => {
        axiosMock.get.mockResolvedValue({
            data: {
                data: {
                    id: 7,
                    name: 'Balón profesional',
                    sku: 'BAL-001',
                    category: 'Implementos',
                    description: 'Balón número 5',
                    entry_price: '70000.00',
                    unit_price: '120000.00',
                    stock_quantity: 4,
                    minimum_stock: 1,
                    is_active: true,
                },
            },
        })
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState

        await state.openEditProduct(7)

        expect(axiosMock.get).toHaveBeenCalledWith('/api/v2/inventory/products/7')
        expect(state.productForm.name).toBe('Balón profesional')
        expect(state.productForm.entry_price).toBe(70000)
        expect(state.productForm.unit_price).toBe(120000)
        expect(wrapper.text()).toContain('Editar producto')
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('shows product stock in movement modal and blocks exits above available stock', async () => {
        axiosMock.get.mockResolvedValue({
            data: {
                data: {
                    id: 8,
                    name: 'Camiseta',
                    entry_price: '35000.00',
                    unit_price: '60000.00',
                    stock_quantity: 3,
                    minimum_stock: 1,
                },
            },
        })
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState

        await state.openMovementForm(8)
        state.movementForm.type = 'exit'
        await nextTick()

        expect(wrapper.text()).toContain('Stock actual')
        expect(wrapper.text()).toContain('Disponible salida')
        expect(wrapper.text()).toContain('Precio entrada')
        expect(wrapper.text()).toContain('Precio venta')
        expect(wrapper.text()).toContain('Margen estimado de esta salida')
        expect(wrapper.text()).toContain('$25000')
        expect(wrapper.text()).toContain('Puedes registrar una salida máxima de 3 unidades.')

        state.movementForm.type = 'exit'
        state.movementForm.quantity = 4

        await state.saveMovement()

        expect(axiosMock.post).not.toHaveBeenCalled()
        expect(state.formErrors.quantity).toBe('La salida no puede superar el stock disponible (3).')
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('renders signed stock changes and identifies exits without cost', () => {
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState

        expect(state.renderStockDelta(null, 'display', { stock_delta: 3 })).toContain('+3')
        expect(state.renderStockDelta(null, 'display', { stock_delta: -2 })).toContain('-2')
        expect(state.renderProfitMargin(null, 'display')).toContain('Sin costo')
        wrapper.unmount()
        vi.unstubAllGlobals()
    })

    it('renders totals supplied by the server for all filtered movements', async () => {
        axiosMock.get.mockResolvedValue({
            data: {
                draw: 1,
                recordsTotal: 20,
                recordsFiltered: 12,
                data: [{ type: 'exit', stock_delta: -2 }],
                totals: {
                    stock_delta: -3,
                    exit_cost: '60000.00',
                    exit_sale: '150000.00',
                    profit_margin: '40000.00',
                    missing_cost_exits: 1,
                },
            },
        })
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState
        const callback = vi.fn()

        await state.movementOptions.ajax({ draw: 1 }, callback)

        const footers = Array.from({ length: 12 }, () => ({ innerHTML: '' }))
        const tableApi = {
            column: vi.fn(index => ({
                footer: () => footers[index],
            })),
        }

        state.movementOptions.footerCallback.call({ api: () => tableApi })

        expect(callback).toHaveBeenCalledWith(expect.objectContaining({ recordsFiltered: 12 }))
        expect(footers[4].innerHTML).toBe('-3')
        expect(footers[5].innerHTML).toBe('$60000')
        expect(footers[6].innerHTML).toBe('$150000')
        expect(footers[7].innerHTML).toBe('$40000')
        expect(state.movementTotals.missing_cost_exits).toBe(1)
        await nextTick()
        expect(wrapper.text()).toContain('salida no se incluyó')
        wrapper.unmount()
        vi.unstubAllGlobals()
    })
})
