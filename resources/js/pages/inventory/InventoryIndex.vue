<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Inventario</h4>
                    <p class="text-muted mb-0">Administra productos, stock y movimientos de inventario.</p>
                </div>
                <button v-if="activeTab === 'products'" type="button" class="btn btn-info btn-sm" @click="openProductForm()">
                    <i class="fa fa-plus me-2" aria-hidden="true"></i>
                    Nuevo producto
                </button>
            </div>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button type="button" class="nav-link" :class="{ active: activeTab === 'products' }" @click="setActiveTab('products')">
                        Productos
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" :class="{ active: activeTab === 'movements' }" @click="setActiveTab('movements')">
                        Movimientos
                    </button>
                </li>
            </ul>

            <div v-if="globalError" class="alert alert-danger" role="alert">
                {{ globalError }}
            </div>

            <div v-show="activeTab === 'products'" class="table-responsive-md">
                <DatatableTemplate id="inventory_products_table" ref="productsTable" :options="productOptions">
                    <template #thead>
                        <thead class="align-middle">
                            <tr>
                                <th>Producto</th>
                                <th>SKU</th>
                                <th>Categoría</th>
                                <th class="text-end">Precio entrada</th>
                                <th class="text-end">Precio venta</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Mínimo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                    </template>
                    <template #actions="props">
                        <div class="d-inline-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                @click="openEditProduct(props.rowData.id)"
                            >
                                Editar
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-info btn-sm"
                                @click="openMovementForm(props.rowData.id)"
                            >
                                Movimiento
                            </button>
                        </div>
                    </template>
                </DatatableTemplate>
            </div>

            <div v-show="activeTab === 'movements'" class="table-responsive-md">
                <DatatableTemplate id="inventory_movements_table" ref="movementsTable" :options="movementOptions">
                    <template #thead>
                        <thead class="align-middle">
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>SKU</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Entrada</th>
                                <th class="text-end">Venta</th>
                                <th class="text-end">Margen</th>
                                <th class="text-center">Antes</th>
                                <th class="text-center">Después</th>
                                <th>Usuario</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Totales:</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-center"></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>

    <div v-if="showProductModal" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="saveProduct">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ productForm.id ? 'Editar producto' : 'Nuevo producto' }}</h5>
                        <button type="button" class="btn-close" :disabled="isSaving" @click="closeProductForm"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="formMessage" class="alert alert-danger" role="alert">{{ formMessage }}</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-product-name">Nombre</label>
                                <input id="inventory-product-name" v-model.trim="productForm.name" type="text" class="form-control" :class="{ 'is-invalid': formErrors.name }">
                                <div v-if="formErrors.name" class="invalid-feedback">{{ formErrors.name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-product-sku">SKU</label>
                                <input id="inventory-product-sku" v-model.trim="productForm.sku" type="text" class="form-control" :class="{ 'is-invalid': formErrors.sku }">
                                <div v-if="formErrors.sku" class="invalid-feedback">{{ formErrors.sku }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-product-category">Categoría</label>
                                <input id="inventory-product-category" v-model.trim="productForm.category" type="text" class="form-control" :class="{ 'is-invalid': formErrors.category }">
                                <div v-if="formErrors.category" class="invalid-feedback">{{ formErrors.category }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-product-entry-price">Precio entrada</label>
                                <CurrencyInput id="inventory-product-entry-price" v-model="productForm.entry_price" class="form-control" :class="{ 'is-invalid': formErrors.entry_price }" />
                                <div v-if="formErrors.entry_price" class="invalid-feedback d-block">{{ formErrors.entry_price }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-product-price">Precio venta</label>
                                <CurrencyInput id="inventory-product-price" v-model="productForm.unit_price" class="form-control" :class="{ 'is-invalid': formErrors.unit_price }" />
                                <div v-if="formErrors.unit_price" class="invalid-feedback d-block">{{ formErrors.unit_price }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-product-stock">Stock actual</label>
                                <input id="inventory-product-stock" v-model.number="productForm.stock_quantity" type="number" min="0" step="1" class="form-control" :class="{ 'is-invalid': formErrors.stock_quantity }" :disabled="Boolean(productForm.id)">
                                <div v-if="formErrors.stock_quantity" class="invalid-feedback">{{ formErrors.stock_quantity }}</div>
                                <small v-if="productForm.id" class="text-muted">El stock se modifica registrando movimientos.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-product-minimum">Stock mínimo</label>
                                <input id="inventory-product-minimum" v-model.number="productForm.minimum_stock" type="number" min="0" step="1" class="form-control" :class="{ 'is-invalid': formErrors.minimum_stock }">
                                <div v-if="formErrors.minimum_stock" class="invalid-feedback">{{ formErrors.minimum_stock }}</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="inventory-product-description">Descripción</label>
                                <textarea id="inventory-product-description" v-model.trim="productForm.description" class="form-control" rows="3" :class="{ 'is-invalid': formErrors.description }"></textarea>
                                <div v-if="formErrors.description" class="invalid-feedback">{{ formErrors.description }}</div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input id="inventory-product-active" v-model="productForm.is_active" class="form-check-input" type="checkbox">
                                    <label class="form-check-label" for="inventory-product-active">Producto activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" :disabled="isSaving" @click="closeProductForm">Cancelar</button>
                        <button type="submit" class="btn btn-info" :disabled="isSaving">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div v-if="showMovementModal" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="saveMovement">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">Registrar movimiento</h5>
                            <small class="text-muted">{{ selectedProduct?.name }} | Stock actual {{ selectedProduct?.stock_quantity }}</small>
                        </div>
                        <button type="button" class="btn-close" :disabled="isSaving" @click="closeMovementForm"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="formMessage" class="alert alert-danger" role="alert">{{ formMessage }}</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="border rounded p-3 bg-light">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-2">
                                            <small class="text-muted d-block">Stock actual</small>
                                            <strong class="h5 mb-0">{{ selectedStock }}</strong>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted d-block">Stock mínimo</small>
                                            <strong class="h5 mb-0">{{ selectedMinimumStock }}</strong>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted d-block">Disponible salida</small>
                                            <strong class="h5 mb-0">{{ exitAvailableStock }}</strong>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted d-block">Quedaría en</small>
                                            <strong class="h5 mb-0" :class="{ 'text-danger': projectedStock < 0 }">{{ projectedStock }}</strong>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted d-block">Precio entrada</small>
                                            <strong class="h6 mb-0">{{ money(selectedEntryPrice) }}</strong>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted d-block">Precio venta</small>
                                            <strong class="h6 mb-0">{{ money(selectedSalePrice) }}</strong>
                                        </div>
                                    </div>
                                    <div v-if="movementForm.type === 'exit'" class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Margen estimado de esta salida</span>
                                        <strong :class="{ 'text-danger': projectedMargin < 0, 'text-success': projectedMargin > 0 }">{{ money(projectedMargin) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-movement-type">Tipo</label>
                                <select id="inventory-movement-type" v-model="movementForm.type" class="form-select" :class="{ 'is-invalid': formErrors.type }">
                                    <option value="entry">Entrada</option>
                                    <option value="exit">Salida</option>
                                    <option value="adjustment">Ajuste</option>
                                </select>
                                <div v-if="formErrors.type" class="invalid-feedback">{{ formErrors.type }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-movement-date">Fecha</label>
                                <input id="inventory-movement-date" v-model="movementForm.movement_date" type="date" class="form-control" :class="{ 'is-invalid': formErrors.movement_date }">
                                <div v-if="formErrors.movement_date" class="invalid-feedback">{{ formErrors.movement_date }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="inventory-movement-quantity">{{ movementForm.type === 'adjustment' ? 'Stock final' : 'Cantidad' }}</label>
                                <input id="inventory-movement-quantity" v-model.number="movementForm.quantity" type="number" min="0" :max="movementForm.type === 'exit' ? exitAvailableStock : null" step="1" class="form-control" :class="{ 'is-invalid': formErrors.quantity }">
                                <div v-if="formErrors.quantity" class="invalid-feedback">{{ formErrors.quantity }}</div>
                                <small v-if="movementForm.type === 'exit'" class="text-muted">
                                    Puedes registrar una salida máxima de {{ exitAvailableStock }} unidades.
                                </small>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="inventory-movement-reason">Motivo</label>
                                <input id="inventory-movement-reason" v-model.trim="movementForm.reason" type="text" class="form-control" :class="{ 'is-invalid': formErrors.reason }">
                                <div v-if="formErrors.reason" class="invalid-feedback">{{ formErrors.reason }}</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="inventory-movement-notes">Notas</label>
                                <textarea id="inventory-movement-notes" v-model.trim="movementForm.notes" class="form-control" rows="3" :class="{ 'is-invalid': formErrors.notes }"></textarea>
                                <div v-if="formErrors.notes" class="invalid-feedback">{{ formErrors.notes }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" :disabled="isSaving" @click="closeMovementForm">Cancelar</button>
                        <button type="submit" class="btn btn-info" :disabled="isSaving">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div v-if="showProductModal || showMovementModal" class="modal-backdrop fade show"></div>
    <breadcrumb :parent="'Plataforma'" :current="'Inventario'" />
</template>

<script setup>
import { computed, reactive, ref, useTemplateRef, watch } from 'vue'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import CurrencyInput from '@/components/general/CurrencyInput'
import api from '@/utils/axios'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTitle } from '@/composables/use-meta'

const props = defineProps({
    initialTab: { type: String, default: 'products' },
})

usePageTitle('Inventario')

const activeTab = ref(props.initialTab === 'movements' ? 'movements' : 'products')
const productsTable = useTemplateRef('productsTable')
const movementsTable = useTemplateRef('movementsTable')
const globalError = ref('')
const formMessage = ref('')
const formErrors = reactive({})
const showProductModal = ref(false)
const showMovementModal = ref(false)
const isSaving = ref(false)
const selectedProduct = ref(null)

const emptyProductForm = () => ({
    id: null,
    name: '',
    sku: '',
    category: '',
    description: '',
    entry_price: 0,
    unit_price: 0,
    stock_quantity: 0,
    minimum_stock: 0,
    is_active: true,
})

const emptyMovementForm = () => ({
    type: 'entry',
    quantity: 1,
    price_snapshot: 0,
    reason: '',
    notes: '',
    movement_date: new Date().toISOString().slice(0, 10),
})

const productForm = reactive(emptyProductForm())
const movementForm = reactive(emptyMovementForm())

const selectedStock = computed(() => Number(selectedProduct.value?.stock_quantity || 0))
const selectedMinimumStock = computed(() => Number(selectedProduct.value?.minimum_stock || 0))
const selectedEntryPrice = computed(() => Number(selectedProduct.value?.entry_price || 0))
const selectedSalePrice = computed(() => Number(selectedProduct.value?.unit_price || 0))
const exitAvailableStock = computed(() => Math.max(selectedStock.value, 0))
const projectedStock = computed(() => {
    const quantity = Number(movementForm.quantity || 0)

    if (movementForm.type === 'entry') {
        return selectedStock.value + quantity
    }

    if (movementForm.type === 'exit') {
        return selectedStock.value - quantity
    }

    return quantity
})
const projectedMargin = computed(() => {
    if (movementForm.type !== 'exit') {
        return 0
    }

    return (selectedSalePrice.value - selectedEntryPrice.value) * Number(movementForm.quantity || 0)
})

watch(() => props.initialTab, (tab) => {
    setActiveTab(tab === 'movements' ? 'movements' : 'products')
})

function money(value) {
    return typeof moneyFormat === 'function' ? moneyFormat(Number(value) || 0) : Number(value || 0).toLocaleString('es-CO')
}

function resetErrors() {
    formMessage.value = ''
    Object.keys(formErrors).forEach((key) => delete formErrors[key])
}

function applyErrors(error, fallback) {
    const errors = error.response?.data?.errors || {}
    Object.keys(errors).forEach((key) => {
        formErrors[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key]
    })
    formMessage.value = error.response?.data?.message || fallback
}

function createTextFilter(column, placeholder, type = 'search') {
    const input = document.createElement('input')
    input.type = type
    input.placeholder = placeholder
    input.className = 'form-control form-control-sm'
    const handler = function () {
        if (column.search() !== this.value) {
            column.search(this.value).draw()
        }
    }
    input.addEventListener(type === 'date' ? 'change' : 'input', handler)
    column.header().replaceChildren(input)
}

function createSelectFilter(column, options) {
    const select = document.createElement('select')
    select.className = 'form-select form-select-sm'
    options.forEach((item) => {
        const option = document.createElement('option')
        option.value = item.value
        option.textContent = item.label
        select.append(option)
    })
    select.addEventListener('change', function () {
        if (column.search() !== this.value) {
            column.search(this.value).draw()
        }
    })
    column.header().replaceChildren(select)
}

function renderStock(data, type, row) {
    const badge = row.is_low_stock ? 'badge-warning' : 'badge-success'
    return `<span class="badge ${badge}">${data}</span>`
}

function renderStatus(data) {
    return data ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>'
}

function renderType(data) {
    const labels = {
        entry: ['Entrada', 'badge-success'],
        exit: ['Salida', 'badge-danger'],
        adjustment: ['Ajuste', 'badge-warning'],
    }
    const [label, cssClass] = labels[data] || [data, 'badge-secondary']
    return `<span class="badge ${cssClass}">${label}</span>`
}

const productColumns = [
    { data: 'name', name: 'name', orderable: false },
    { data: 'sku', name: 'sku', orderable: false, render: data => data || '<span class="text-muted">-</span>' },
    { data: 'category', name: 'category', orderable: false, render: data => data || '<span class="text-muted">-</span>' },
    { data: 'entry_price', name: 'entry_price', searchable: false, render: data => money(data), className: 'dt-body-right' },
    { data: 'unit_price', name: 'unit_price', searchable: false, render: data => money(data), className: 'dt-body-right' },
    { data: 'stock_quantity', name: 'stock_quantity', searchable: false, render: renderStock, className: 'dt-head-center dt-body-center' },
    { data: 'minimum_stock', name: 'minimum_stock', searchable: false, className: 'dt-head-center dt-body-center' },
    { data: 'is_active', name: 'is_active', orderable: false, render: renderStatus, className: 'dt-head-center dt-body-center' },
    { data: 'id', searchable: false, orderable: false, render: '#actions', className: 'dt-head-center dt-body-center' },
]

const productOptions = {
    ...configLanguaje,
    layout: {
        topStart: { pageLength: { menu: [10, 20, 30, 50, 100] } },
        topEnd: null,
        bottomStart: 'info',
        bottomEnd: 'paging',
    },
    serverSide: true,
    pipeline: { pages: 5 },
    processing: true,
    order: [[0, 'asc']],
    ajax: async (data, callback) => {
        try {
            globalError.value = ''
            const response = await api.get('/api/v2/datatables/inventory_products', { params: data })
            callback(response.data)
        } catch (error) {
            globalError.value = 'No fue posible cargar los productos de inventario.'
            callback({ draw: data.draw, data: [], recordsTotal: 0, recordsFiltered: 0 })
        }
    },
    columns: productColumns,
    initComplete: function () {
        const tableApi = this.api()
        createTextFilter(tableApi.column(0), 'Producto')
        createTextFilter(tableApi.column(1), 'SKU')
        createTextFilter(tableApi.column(2), 'Categoría')
        createSelectFilter(tableApi.column(7), [
            { value: '', label: 'Estado' },
            { value: '1', label: 'Activo' },
            { value: '0', label: 'Inactivo' },
        ])
    },
}

const movementColumns = [
    { data: 'movement_date', name: 'movement_date', orderable: false },
    { data: 'product_name', name: 'product_name', orderable: false },
    { data: 'product_sku', name: 'product_sku', render: data => data || '<span class="text-muted">-</span>' },
    { data: 'type', name: 'type', orderable: false, render: renderType, className: 'dt-head-center dt-body-center' },
    { data: 'quantity', name: 'quantity', searchable: false, className: 'dt-head-center dt-body-center' },
    { data: 'entry_price_snapshot', name: 'entry_price_snapshot', searchable: false, render: data => money(data), className: 'dt-body-right' },
    { data: 'sale_price_snapshot', name: 'sale_price_snapshot', searchable: false, render: data => money(data), className: 'dt-body-right' },
    { data: 'profit_margin', name: 'profit_margin', searchable: false, render: data => money(data), className: 'dt-body-right' },
    { data: 'stock_before', name: 'stock_before', searchable: false, className: 'dt-head-center dt-body-center' },
    { data: 'stock_after', name: 'stock_after', searchable: false, className: 'dt-head-center dt-body-center' },
    { data: 'user_name', name: 'user_name', orderable: false },
    { data: 'reason', name: 'reason', render: data => data || '<span class="text-muted">-</span>' },
]

const movementOptions = {
    ...configLanguaje,
    layout: {
        topStart: { pageLength: { menu: [10, 20, 30, 50, 100] } },
        topEnd: null,
        bottomStart: 'info',
        bottomEnd: 'paging',
    },
    serverSide: true,
    pipeline: { pages: 5 },
    processing: true,
    order: [[0, 'desc']],
    ajax: async (data, callback) => {
        try {
            globalError.value = ''
            const response = await api.get('/api/v2/datatables/inventory_movements', { params: data })
            callback(response.data)
        } catch (error) {
            globalError.value = 'No fue posible cargar los movimientos de inventario.'
            callback({ draw: data.draw, data: [], recordsTotal: 0, recordsFiltered: 0 })
        }
    },
    columns: movementColumns,
    initComplete: function () {
        const tableApi = this.api()
        createTextFilter(tableApi.column(0), 'Fecha', 'date')
        createTextFilter(tableApi.column(1), 'Producto')
        createSelectFilter(tableApi.column(3), [
            { value: '', label: 'Tipo' },
            { value: 'entry', label: 'Entrada' },
            { value: 'exit', label: 'Salida' },
            { value: 'adjustment', label: 'Ajuste' },
        ])
        createTextFilter(tableApi.column(10), 'Usuario')
    },
    footerCallback: function () {
        const tableApi = this.api()
        const currentRows = tableApi.rows({ page: 'current' }).data().toArray()
        const exitRows = currentRows.filter(row => row.type === 'exit')
        const quantityTotal = currentRows.reduce((sum, row) => sum + (Number(row.quantity) || 0), 0)
        const entryTotal = exitRows.reduce((sum, row) => sum + ((Number(row.quantity) || 0) * (Number(row.entry_price_snapshot) || 0)), 0)
        const saleTotal = exitRows.reduce((sum, row) => sum + ((Number(row.quantity) || 0) * (Number(row.sale_price_snapshot) || 0)), 0)
        const marginTotal = exitRows.reduce((sum, row) => sum + (Number(row.profit_margin) || 0), 0)
        tableApi.column(4).footer().innerHTML = String(quantityTotal)
        tableApi.column(5).footer().innerHTML = money(entryTotal)
        tableApi.column(6).footer().innerHTML = money(saleTotal)
        tableApi.column(7).footer().innerHTML = money(marginTotal)
    },
}

function reloadProducts() {
    productsTable.value?.table?.dt?.ajax?.reload(null, false)
}

function reloadMovements() {
    movementsTable.value?.table?.dt?.ajax?.reload(null, false)
}

function setActiveTab(tab) {
    activeTab.value = tab

    if (tab === 'movements') {
        reloadMovements()
    }
}

function assignProductForm(product = emptyProductForm()) {
    Object.assign(productForm, {
        ...emptyProductForm(),
        ...product,
        entry_price: Number(product.entry_price || 0),
        unit_price: Number(product.unit_price || 0),
        stock_quantity: Number(product.stock_quantity || 0),
        minimum_stock: Number(product.minimum_stock || 0),
        is_active: Boolean(product.is_active ?? true),
    })
}

function assignMovementForm(product) {
    Object.assign(movementForm, {
        ...emptyMovementForm(),
        price_snapshot: Number(product?.unit_price || 0),
    })
}

function openProductForm(product = null) {
    resetErrors()
    assignProductForm(product || emptyProductForm())
    showProductModal.value = true
}

async function openEditProduct(productId) {
    resetErrors()
    const response = await api.get(`/api/v2/inventory/products/${productId}`)
    openProductForm(response.data.data)
}

async function openMovementForm(productId) {
    resetErrors()
    const response = await api.get(`/api/v2/inventory/products/${productId}`)
    selectedProduct.value = response.data.data
    assignMovementForm(selectedProduct.value)
    showMovementModal.value = true
}

function closeProductForm() {
    if (isSaving.value) return
    showProductModal.value = false
}

function closeMovementForm() {
    if (isSaving.value) return
    showMovementModal.value = false
    selectedProduct.value = null
}

async function saveProduct() {
    resetErrors()
    isSaving.value = true
    try {
        const payload = { ...productForm }
        if (payload.id) {
            await api.put(`/api/v2/inventory/products/${payload.id}`, payload)
        } else {
            await api.post('/api/v2/inventory/products', payload)
        }
        showMessage('Producto guardado correctamente')
        showProductModal.value = false
        reloadProducts()
        reloadMovements()
    } catch (error) {
        applyErrors(error, 'No fue posible guardar el producto.')
    } finally {
        isSaving.value = false
    }
}

async function saveMovement() {
    resetErrors()
    if (!validateMovementForm()) {
        return
    }
    isSaving.value = true
    try {
        await api.post(`/api/v2/inventory/products/${selectedProduct.value.id}/movements`, { ...movementForm })
        showMessage('Movimiento registrado correctamente')
        showMovementModal.value = false
        selectedProduct.value = null
        reloadProducts()
        reloadMovements()
    } catch (error) {
        applyErrors(error, 'No fue posible registrar el movimiento.')
    } finally {
        isSaving.value = false
    }
}

function validateMovementForm() {
    const allowedTypes = ['entry', 'exit', 'adjustment']
    if (!allowedTypes.includes(movementForm.type)) {
        formErrors.type = 'Selecciona un tipo de movimiento válido.'
    }

    if (Number(movementForm.quantity) < 0 || (movementForm.type !== 'adjustment' && Number(movementForm.quantity) < 1)) {
        formErrors.quantity = movementForm.type === 'adjustment'
            ? 'El stock final no puede ser negativo.'
            : 'La cantidad debe ser mayor a cero.'
    }

    if (movementForm.type === 'exit' && Number(movementForm.quantity) > exitAvailableStock.value) {
        formErrors.quantity = `La salida no puede superar el stock disponible (${exitAvailableStock.value}).`
    }

    if (!movementForm.movement_date) {
        formErrors.movement_date = 'La fecha es obligatoria.'
    }

    const hasErrors = Object.keys(formErrors).length > 0
    if (hasErrors) {
        formMessage.value = 'Revisa los campos del movimiento.'
    }

    return !hasErrors
}

</script>
