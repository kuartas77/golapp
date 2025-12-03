<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <!-- Información de la factura -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-invoice"></i> Factura #{{ invoice.invoice_number }}
                        </h4>
                        <span :class="`badge badge-${getStatusClass(invoice.status)}`">
                            {{ invoice.status.toUpperCase() }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Información del Estudiante</h5>
                                <p><strong>Nombre:</strong> {{ invoice.student_name }}</p>
                                <p><strong>Grupo:</strong> {{ invoice.training_group?.name || 'N/A' }}</p>
                                <p><strong>Año:</strong> {{ invoice.year }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h5>Detalles de Factura</h5>
                                <p><strong>Fecha Emisión:</strong> {{ formatDate(invoice.issue_date) }}</p>
                                <p><strong>Fecha Vencimiento:</strong> {{ formatDate(invoice.due_date) }}</p>
                                <p><strong>Creada por:</strong> {{ invoice.creator?.name || 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Ítems de la factura -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-right">Precio Unitario</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in invoice.items" :key="item.id">
                                        <td>
                                            <span :class="`badge badge-${getItemTypeClass(item.type)}`">
                                                {{ getItemTypeLabel(item.type) }}
                                            </span>
                                            <br v-if="item.month">
                                            <small v-if="item.month">{{ capitalize(item.month) }}</small>
                                        </td>
                                        <td>{{ item.description }}</td>
                                        <td class="text-center">{{ item.quantity }}</td>
                                        <td class="text-right">${{ formatNumber(item.unit_price) }}</td>
                                        <td class="text-right">${{ formatNumber(item.total) }}</td>
                                        <td class="text-center">
                                            <span :class="`badge badge-${item.is_paid ? 'success' : 'warning'}`">
                                                {{ item.is_paid ? 'Pagado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Total Factura:</strong></td>
                                        <td class="text-right"><strong>${{ formatNumber(invoice.total_amount) }}</strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Pagado:</strong></td>
                                        <td class="text-right"><strong>${{ formatNumber(invoice.paid_amount) }}</strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Saldo Pendiente:</strong></td>
                                        <td class="text-right">
                                            <strong :class="balance > 0 ? 'text-danger' : 'text-success'">
                                                ${{ formatNumber(balance) }}
                                            </strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Notas -->
                        <div v-if="invoice.notes" class="alert alert-info">
                            <h6><i class="fas fa-sticky-note"></i> Notas:</h6>
                            <p>{{ invoice.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Historial de pagos -->
                <div v-if="invoice.payments?.length > 0" class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5><i class="fas fa-history"></i> Historial de Pagos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Método</th>
                                        <th>Referencia</th>
                                        <th class="text-right">Monto</th>
                                        <th>Registrado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="payment in invoice.payments" :key="payment.id">
                                        <td>{{ formatDate(payment.payment_date) }}</td>
                                        <td>
                                            <span :class="`badge badge-${getPaymentMethodClass(payment.payment_method)}`">
                                                {{ getPaymentMethodLabel(payment.payment_method) }}
                                            </span>
                                        </td>
                                        <td>{{ payment.reference || 'N/A' }}</td>
                                        <td class="text-right">${{ formatNumber(payment.amount) }}</td>
                                        <td>{{ payment.creator?.name || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Panel de pagos -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-money-bill-wave"></i> Registrar Pago</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitPayment">
                            <div class="form-group">
                                <label>Monto a Pagar *</label>
                                <input type="number"
                                       class="form-control"
                                       v-model="payment.amount"
                                       :max="balance"
                                       step="0.01"
                                       min="0.01"
                                       required>
                            </div>

                            <div class="form-group">
                                <label>Método de Pago *</label>
                                <select class="form-control" v-model="payment.payment_method" required>
                                    <option value="cash">Efectivo</option>
                                    <option value="card">Tarjeta</option>
                                    <option value="transfer">Transferencia</option>
                                    <option value="check">Cheque</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Referencia</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="payment.reference"
                                       placeholder="Nº de transacción, cheque, etc.">
                            </div>

                            <div class="form-group">
                                <label>Fecha del Pago *</label>
                                <input type="date"
                                       class="form-control"
                                       v-model="payment.payment_date"
                                       required>
                            </div>

                            <div class="form-group">
                                <label>Notas</label>
                                <textarea class="form-control"
                                          v-model="payment.notes"
                                          rows="2"></textarea>
                            </div>

                            <!-- Ítems a marcar como pagados -->
                            <div v-if="unpaidItems.length > 0" class="form-group">
                                <label>Marcar ítems como pagados (opcional):</label>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                    <div v-for="item in unpaidItems" :key="item.id" class="form-check">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               :value="item.id"
                                               v-model="payment.paid_items"
                                               :id="`item_${item.id}`">
                                        <label class="form-check-label" :for="`item_${item.id}`">
                                            {{ item.description }} - ${{ formatNumber(item.total) }}
                                            <small v-if="item.month" class="text-muted">
                                                ({{ capitalize(item.month) }})
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                    class="btn btn-success btn-block"
                                    :disabled="paymentLoading">
                                <span v-if="paymentLoading" class="spinner-border spinner-border-sm"></span>
                                <i v-else class="fas fa-check-circle"></i>
                                {{ paymentLoading ? 'Registrando...' : 'Registrar Pago' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-cogs"></i> Acciones</h5>
                    </div>
                    <div class="card-body">
                        <button @click="goToList" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-list"></i> Volver al Listado
                        </button>

                        <button @click="printInvoice" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-print"></i> Imprimir Factura
                        </button>

                        <button v-if="canDelete"
                                @click="confirmDelete"
                                class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Eliminar Factura
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const invoiceId = route.params.id

// Estado reactivo
const invoice = ref({ items: [], payments: [] })
const loading = ref(true)
const paymentLoading = ref(false)
const canDelete = ref(true)

// Formulario de pago
const payment = reactive({
    amount: 0,
    payment_method: 'cash',
    reference: '',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
    paid_items: []
})

// Computed
const balance = computed(() => {
    return invoice.value.total_amount - invoice.value.paid_amount
})

const unpaidItems = computed(() => {
    return invoice.value.items?.filter(item => !item.is_paid) || []
})

// Métodos
const loadInvoice = async () => {
    try {
        loading.value = true
        const response = await axios.get(`/api/invoices/${invoiceId}`)
        invoice.value = response.data

        // Establecer monto máximo de pago
        payment.amount = balance.value > 0 ? balance.value : 0

    } catch (error) {
        console.error('Error al cargar factura:', error)
        alert('Error al cargar la factura')
    } finally {
        loading.value = false
    }
}

const submitPayment = async () => {
    if (payment.amount <= 0) {
        alert('El monto debe ser mayor a 0')
        return
    }

    if (payment.amount > balance.value) {
        alert('El monto no puede ser mayor al saldo pendiente')
        return
    }

    try {
        paymentLoading.value = true

        const response = await axios.post(`/api/invoices/${invoiceId}/payment`, payment)

        // Recargar la factura para actualizar datos
        await loadInvoice()

        // Resetear formulario
        resetPaymentForm()

        alert('Pago registrado exitosamente')

    } catch (error) {
        console.error('Error al registrar pago:', error)
        alert('Error al registrar el pago')
    } finally {
        paymentLoading.value = false
    }
}

const resetPaymentForm = () => {
    payment.amount = balance.value > 0 ? balance.value : 0
    payment.reference = ''
    payment.notes = ''
    payment.paid_items = []
    payment.payment_date = new Date().toISOString().split('T')[0]
}

const printInvoice = () => {
    window.print()
}

const goToList = () => {
    router.push('/invoices')
}

const confirmDelete = async () => {
    if (confirm('¿Está seguro de eliminar esta factura?')) {
        try {
            await axios.delete(`/api/invoices/${invoiceId}`)
            router.push('/invoices')
        } catch (error) {
            console.error('Error al eliminar factura:', error)
            alert('Error al eliminar la factura')
        }
    }
}

// Métodos de utilidad
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    const date = new Date(dateString)
    return date.toLocaleDateString('es-ES')
}

const formatNumber = (number) => {
    return parseFloat(number).toFixed(2)
}

const capitalize = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1)
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

const getItemTypeClass = (type) => {
    const classes = {
        'monthly': 'info',
        'enrollment': 'primary',
        'additional': 'secondary'
    }
    return classes[type] || 'secondary'
}

const getItemTypeLabel = (type) => {
    const labels = {
        'monthly': 'Mensualidad',
        'enrollment': 'Matrícula',
        'additional': 'Adicional'
    }
    return labels[type] || type
}

const getPaymentMethodClass = (method) => {
    const classes = {
        'cash': 'success',
        'card': 'primary',
        'transfer': 'info',
        'check': 'warning',
        'other': 'secondary'
    }
    return classes[method] || 'secondary'
}

const getPaymentMethodLabel = (method) => {
    const labels = {
        'cash': 'Efectivo',
        'card': 'Tarjeta',
        'transfer': 'Transferencia',
        'check': 'Cheque',
        'other': 'Otro'
    }
    return labels[method] || method
}

// Cargar datos al montar
onMounted(() => {
    loadInvoice()
})
</script>

<style scoped>
.badge {
    font-size: 0.8em;
}

@media print {
    .card-header.bg-primary,
    .card-header.bg-success,
    .card-header.bg-info,
    .card-header.bg-secondary {
        background-color: #fff !important;
        color: #000 !important;
        border-bottom: 2px solid #000;
    }

    .btn, .form-group {
        display: none !important;
    }
}
</style>