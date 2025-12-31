<template>
    <panel>
        <template #body>

            <div class="row ">
                <div class="col-md-2">
                    <div class="form-group">
                        <select class="form-select form-select-sm" id="filterStatus" >
                            <option value="">Todos los estados</option>
                            <option value="pending">Pendiente</option>
                            <option value="partial">Parcial</option>
                            <option value="paid">Pagada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <flat-pickr :config="flatpickrConfig" class="form-control form-control-sm flatpickr"
                            id="filterDate" v-model="filterDate" placeholder="Rango fecha facturación"></flat-pickr>
                    </div>
                </div>
            </div>

            <DatatableTemplate :options="options" :id="'invoives_table'" ref="invoives_table" @click="onClickRow">
                <template #thead>
                    <thead>
                        <tr>
                            <th># Factura</th>
                            <th>Deportista</th>
                            <th>Grupo</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Pagado</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Totales:</th>
                            <th></th>
                            <th></th>
                            <th class="text-right"></th>
                            <th class="text-right"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </template>
            </DatatableTemplate>

        </template>

    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Facturas'" />

</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import useInvoicesList from '@/composables/invoices/invoicesList'
import { useRouter } from 'vue-router'
import axios from 'axios'
import dayjs from '@/utils/dayjs';
import { debounce } from 'lodash-es';
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";

const flatpickrConfig = {
    wrap: true,
    mode: "range",
    locale: Spanish,
    maxDate: dayjs().format('YYYY-M-D'),
    minDate: dayjs().subtract(5, 'year').format('YYYY-M-D'),
}

const { options, invoives_table, onClickRow, reloadTable } = useInvoicesList()

const router = useRouter()

// Estado reactivo
const filterDate = ref('')
const invoices = ref([])
const loading = ref(false)
const search = ref('')
const totalInvoices = ref(0)

// Filtros
const filters = reactive({
    status: '',
    year: new Date().getFullYear()
})

// Ordenamiento
const sort = reactive({
    field: 'created_at',
    order: 'desc'
})

// Paginación
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0
})

// Computed
const filteredInvoices = computed(() => {
    let filtered = invoices.value

    // Aplicar búsqueda
    if (search.value) {
        const term = search.value.toLowerCase()
        filtered = filtered.filter(invoice =>
            invoice.invoice_number.toLowerCase().includes(term) ||
            invoice.student_name.toLowerCase().includes(term)
        )
    }

    // Aplicar filtro por estado
    if (filters.status) {
        filtered = filtered.filter(invoice => invoice.status === filters.status)
    }

    // Aplicar filtro por año
    if (filters.year) {
        filtered = filtered.filter(invoice => invoice.year == filters.year)
    }

    // Aplicar ordenamiento
    filtered.sort((a, b) => {
        let aValue = a[sort.field]
        let bValue = b[sort.field]

        // Manejar campos anidados
        if (sort.field === 'training_group') {
            aValue = a.training_group?.name || ''
            bValue = b.training_group?.name || ''
        }

        if (sort.order === 'asc') {
            return aValue > bValue ? 1 : -1
        } else {
            return aValue < bValue ? 1 : -1
        }
    })

    return filtered
})

const visiblePages = computed(() => {
    const pages = []
    const maxVisible = 5
    let start = Math.max(1, pagination.current_page - Math.floor(maxVisible / 2))
    let end = Math.min(pagination.last_page, start + maxVisible - 1)

    // Ajustar inicio si estamos cerca del final
    if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

// Métodos
const loadInvoices = async () => {
    try {
        // loading.value = true

        // const params = {
        //     page: pagination.current_page,
        //     per_page: pagination.per_page,
        //     status: filters.status,
        //     year: filters.year,
        //     sort: sort.field,
        //     order: sort.order
        // }

        // const response = await axios.get('/api/v2/invoices', { params })

        // invoices.value = response.data.data
        // pagination.current_page = response.data.current_page
        // pagination.last_page = response.data.last_page
        // pagination.total = response.data.total
        // totalInvoices.value = response.data.total

    } catch (error) {
        console.error('Error al cargar facturas:', error)
        alert('Error al cargar las facturas')
    } finally {
        loading.value = false
    }
}

const sortBy = (field) => {
    if (sort.field === field) {
        sort.order = sort.order === 'asc' ? 'desc' : 'asc'
    } else {
        sort.field = field
        sort.order = 'asc'
    }
    loadInvoices()
}

const changePage = (page) => {
    if (page >= 1 && page <= pagination.last_page && page !== pagination.current_page) {
        pagination.current_page = page
        loadInvoices()
    }
}

const resetFilters = () => {
    filters.status = ''
    filters.year = new Date().getFullYear()
    loadInvoices()
}

const viewInvoice = (id) => {
    router.push({ name: 'invoices.show', params: { id: id } })
}

const deleteInvoice = async (id, invoiceNumber) => {
    if (confirm(`¿Está seguro de eliminar la factura ${invoiceNumber}?`)) {
        try {
            await axios.delete(`/api/v2/invoices/${id}`)
            loadInvoices()
            alert('Factura eliminada exitosamente')
        } catch (error) {
            console.error('Error al eliminar factura:', error)
            alert('Error al eliminar la factura')
        }
    }
}

const canDeleteInvoice = (invoice) => {
    // Solo permitir eliminar facturas pendientes o parciales
    return ['pending', 'partial'].includes(invoice.status)
}

// Métodos de utilidad
const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('es-ES')
}

const getStatusClass = (status) => {
    const classes = {
        'paid': 'success',
        'partial': 'warning',
        'pending': 'danger',
        'cancelled': 'secondary'
    }
    return classes[status] || 'secondary'
}

const getStatusLabel = (status) => {
    const labels = {
        'paid': 'Pagada',
        'partial': 'Parcial',
        'pending': 'Pendiente',
        'cancelled': 'Cancelada'
    }
    return labels[status] || status
}

// Búsqueda con debounce
const debouncedSearch = debounce(() => {
    loadInvoices()
}, 500)

// Watch para búsqueda
watch(search, () => {
    if (search.value.length === 0 || search.value.length >= 3) {
        debouncedSearch()
    }
})

// Cargar datos iniciales
onMounted(() => {
    loadInvoices()
})
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.cursor-pointer:hover {
    background-color: #f8f9fa;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.badge {
    font-size: 0.85em;
    padding: 0.25em 0.6em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>