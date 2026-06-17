import { mount } from '@vue/test-utils'
import { defineComponent, nextTick } from 'vue'
import { describe, expect, it, vi } from 'vitest'

const { axiosMock, productReloadMock, movementReloadMock } = vi.hoisted(() => ({
    axiosMock: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
    },
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

    it('calculates movement financial totals only from exits', () => {
        const wrapper = mountPage()
        const state = wrapper.vm.$.setupState
        const footers = Array.from({ length: 12 }, () => ({ innerHTML: '' }))
        const tableApi = {
            rows: vi.fn(() => ({
                data: () => ({
                    toArray: () => [
                        {
                            type: 'adjustment',
                            quantity: 100,
                            entry_price_snapshot: 45000,
                            sale_price_snapshot: 50000,
                            profit_margin: 0,
                        },
                        {
                            type: 'exit',
                            quantity: 10,
                            entry_price_snapshot: 45000,
                            sale_price_snapshot: 50000,
                            profit_margin: 50000,
                        },
                    ],
                }),
            })),
            column: vi.fn(index => ({
                footer: () => footers[index],
            })),
        }

        state.movementOptions.footerCallback.call({ api: () => tableApi })

        expect(footers[4].innerHTML).toBe('110')
        expect(footers[5].innerHTML).toBe('$450000')
        expect(footers[6].innerHTML).toBe('$500000')
        expect(footers[7].innerHTML).toBe('$50000')
        wrapper.unmount()
        vi.unstubAllGlobals()
    })
})
