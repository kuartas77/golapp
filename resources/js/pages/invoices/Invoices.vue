<template>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-file-invoice"></i> Facturas
                </h4>
                <div>
                    <input type="text"
                           class="form-control form-control-sm"
                           placeholder="Buscar..."
                           v-model="search"
                           style="width: 200px; display: inline-block;">
                </div>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control form-control-sm" v-model="filters.status">
                            <option value="">Todos los estados</option>
                            <option value="pending">Pendiente</option>
                            <option value="partial">Parcial</option>
                            <option value="paid">Pagada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number"
                               class="form-control form-control-sm"
                               placeholder="Año"
                               v-model="filters.year">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-sm btn-primary" @click="loadInvoices">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <button class="btn btn-sm btn-secondary" @click="resetFilters">
                            <i class="fas fa-redo"></i> Limpiar
                        </button>
                    </div>
                </div>

                <!-- Tabla de facturas -->
                <div v-if="loading" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>

                <div v-else>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th @click="sortBy('invoice_number')" class="cursor-pointer">
                                        # Factura
                                        <i v-if="sort.field === 'invoice_number'"
                                           :class="`fas fa-sort-${sort.order === 'asc' ? 'up' : 'down'}`"></i>
                                    </th>
                                    <th @click="sortBy('student_name')" class="cursor-pointer">
                                        Estudiante
                                        <i v-if="sort.field === 'student_name'"
                                           :class="`fas fa-sort-${sort.order === 'asc' ? 'up' : 'down'}`"></i>
                                    </th>
                                    <th>Grupo</th>
                                    <th @click="sortBy('total_amount')" class="cursor-pointer">
                                        Total
                                        <i v-if="sort.field === 'total_amount'"
                                           :class="`fas fa-sort-${sort.order === 'asc' ? 'up' : 'down'}`"></i>
                                    </th>
                                    <th>Pagado</th>
                                    <th @click="sortBy('status')" class="cursor-pointer">
                                        Estado
                                        <i v-if="sort.field === 'status'"
                                           :class="`fas fa-sort-${sort.order === 'asc' ? 'up' : 'down'}`"></i>
                                    </th>
                                    <th @click="sortBy('created_at')" class="cursor-pointer">
                                        Fecha
                                        <i v-if="sort.field === 'created_at'"
                                           :class="`fas fa-sort-${sort.order === 'asc' ? 'up' : 'down'}`"></i>
                                    </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in filteredInvoices" :key="invoice.id">
                                    <td>
                                        <strong>{{ invoice.invoice_number }}</strong>
                                    </td>
                                    <td>{{ invoice.student_name }}</td>
                                    <td>{{ invoice.training_group?.name || 'N/A' }}</td>
                                    <td>${{ formatNumber(invoice.total_amount) }}</td>
                                    <td>${{ formatNumber(invoice.paid_amount) }}</td>
                                    <td>
                                        <span :class="`badge badge-${getStatusClass(invoice.status)}`">
                                            {{ getStatusLabel(invoice.status) }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate(invoice.created_at) }}</td>
                                    <td>
                                        <button @click="viewInvoice(invoice.id)"
                                                class="btn btn-sm btn-info mr-1"
                                                title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button v-if="canDeleteInvoice(invoice)"
                                                @click="deleteInvoice(invoice.id, invoice.invoice_number)"
                                                class="btn btn-sm btn-danger"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Mostrando {{ filteredInvoices.length }} de {{ totalInvoices }} facturas
                        </div>
                        <nav v-if="pagination.last_page > 1">
                            <ul class="pagination pagination-sm">
                                <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                    <button class="page-link" @click="changePage(1)">«</button>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                    <button class="page-link" @click="changePage(pagination.current_page - 1)">‹</button>
                                </li>

                                <li v-for="page in visiblePages"
                                    :key="page"
                                    class="page-item"
                                    :class="{ active: page === pagination.current_page }">
                                    <button class="page-link" @click="changePage(page)">{{ page }}</button>
                                </li>

                                <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                    <button class="page-link" @click="changePage(pagination.current_page + 1)">›</button>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                    <button class="page-link" @click="changePage(pagination.last_page)">»</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import debounce from 'lodash/debounce'

const router = useRouter()

// Estado reactivo
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
        loading.value = true

        const params = {
            page: pagination.current_page,
            per_page: pagination.per_page,
            status: filters.status,
            year: filters.year,
            sort: sort.field,
            order: sort.order
        }

        const response = await axios.get('/api/invoices', { params })

        invoices.value = response.data.data
        pagination.current_page = response.data.current_page
        pagination.last_page = response.data.last_page
        pagination.total = response.data.total
        totalInvoices.value = response.data.total

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
    router.push(`/invoices/${id}`)
}

const deleteInvoice = async (id, invoiceNumber) => {
    if (confirm(`¿Está seguro de eliminar la factura ${invoiceNumber}?`)) {
        try {
            await axios.delete(`/api/invoices/${id}`)
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

const formatNumber = (number) => {
    return parseFloat(number).toFixed(2)
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