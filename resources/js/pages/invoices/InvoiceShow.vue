<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">

            <div class="col-md-8">
                <!-- Información de la factura -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa fa-file-invoice"></i> Factura #{{ invoice.invoice_number }}
                        </h5>
                        <span :class="`text-uppercase badge badge-${getStatusClass(invoice.status)}`">
                            {{ getStatusLabel(invoice.status) }}
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="">
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
                                        </td>
                                        <td>{{ item.description }}</td>
                                        <td class="text-center">{{ item.quantity }}</td>
                                        <td class="text-right">{{ moneyFormat(item.unit_price) }}</td>
                                        <td class="text-right">{{ moneyFormat(item.total) }}</td>
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
                                        <td class="text-right">
                                            <strong>{{ moneyFormat(invoice.total_amount) }}</strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Pagado:</strong></td>
                                        <td class="text-right">
                                            <strong>{{ moneyFormat(invoice.paid_amount) }}</strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Saldo Pendiente:</strong></td>
                                        <td class="text-right">
                                            <strong :class="balance > 0 ? 'text-danger' : 'text-success'">
                                                {{ moneyFormat(balance) }}
                                            </strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Notas -->
                        <div v-if="invoice.notes" class="alert alert-info mt-2">
                            <h6><i class="fa fa-sticky-note"></i> Notas:</h6>
                            <p>{{ invoice.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Historial de pagos -->
                <div v-if="invoice.payments?.length > 0" class="card">
                    <div class="card-header">
                        <h5><i class="fa fa-history"></i> Historial de Pagos</h5>
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
                                            <span
                                                :class="`badge badge-${getPaymentMethodClass(payment.payment_method)}`">
                                                {{ getPaymentMethodLabel(payment.payment_method) }}
                                            </span>
                                        </td>
                                        <td>{{ payment.reference || 'N/A' }}</td>
                                        <td class="text-right">{{ moneyFormat(payment.amount) }}</td>
                                        <td>{{ payment.creator?.name || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de pagos -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header ">
                        <h5 class="mb-0"><i class="fa fa-money-bill-wave"></i> Registrar Pago</h5>
                    </div>
                    <div class="card-body col-md-12">
                        <form @submit.prevent="submitPayment">
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Ítems a marcar como pagados -->
                                    <div v-if="unpaidItems.length > 0" class="form-group">
                                        <label>Seleccionar ítems a pagar:</label>
                                        <div
                                            style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                            <div v-for="item in unpaidItems" :key="item.id"
                                                class="custom-control custom-checkbox checkbox-primary mb-2">
                                                <input type="checkbox" class="custom-control-input" :value="item.id"
                                                    v-model="payment.paid_items" :id="`item_${item.id}`"
                                                    @change="updatePaymentAmount">
                                                <label class="custom-control-label d-flex justify-content-between w-100"
                                                    :for="`item_${item.id}`">
                                                    <span>
                                                        {{ item.description }}
                                                        <small class="text-muted">({{ getItemTypeLabel(item.type)
                                                        }})</small>
                                                    </span>
                                                    <span class="font-weight-bold">{{ moneyFormat(item.total) }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Seleccione uno o más ítems para calcular el monto a
                                            pagar</small>
                                    </div>
                                    <div v-else class="alert alert-success">
                                        <i class="fa fa-check-circle"></i> Todos los ítems han sido pagados.
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Monto a Pagar <span class="text-danger">&nbsp;(*)</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm"
                                                :value="moneyFormat(calculatedAmount)" disabled
                                                style="background-color: #f8f9fa; font-weight: bold;">
                                            <div class="input-group-append">
                                                <span class="input-group-text"
                                                    style="background-color: #e9ecef; font-weight: bold;">Total</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Monto calculado automáticamente basado en los ítems
                                            seleccionados</small>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                    <div class="form-group">
                                        <label>Método de Pago <span class="text-danger">&nbsp;(*)</span></label>
                                        <select class="form-select form-select-sm" v-model="payment.payment_method"
                                            required>
                                            <option value="cash">Efectivo</option>
                                            <option value="card">Tarjeta</option>
                                            <option value="transfer">Transferencia</option>
                                            <option value="check">Cheque</option>
                                            <option value="other">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                    <div class="form-group">
                                        <label>Referencia</label>
                                        <input type="text" class="form-control form-control-sm"
                                            v-model="payment.reference" placeholder="Nº de transacción, cheque, etc.">
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                    <div class="form-group">
                                        <label>Fecha del Pago <span class="text-danger">&nbsp;(*)</span></label>
                                        <flat-pickr :config="flatpickrConfig" class="form-control form-control-sm flatpickr"
                                        id="filterDate" v-model="payment.payment_date" required></flat-pickr>
                                    </div>
                                </div>
                                <!-- <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Notas</label>
                                        <textarea class="form-control form-control-sm" v-model="payment.notes" rows="2"></textarea>
                                    </div>
                                </div> -->
                                <div class="btn-group">

                                    <button type="submit" class="btn btn-success btn-block"
                                        :disabled="paymentLoading || calculatedAmount <= 0 || payment.paid_items.length === 0">
                                        <span v-if="paymentLoading" class="spinner-border spinner-border-sm"></span>
                                        <i v-else class="fas fa-check-circle"></i>
                                        {{ paymentLoading ? 'Registrando...' : `Pagar
                                        ${moneyFormat(calculatedAmount)}` }}
                                    </button>

                                    <a :href="invoice.url_print" class="btn btn-info btn-block " target="_blank">
                                        <i class="fa fa-print"></i> Imprimir
                                    </a>
                                    <button v-if="canDeleteInvoice(invoice)" type="button" @click="confirmDelete"
                                        class="btn btn-danger btn-block">
                                        <i class="fa fa-trash"></i> Eliminar Factura
                                    </button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import dayjs from '@/utils/dayjs';
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";

const flatpickrConfig = {
    wrap: true,
    locale: Spanish,
    maxDate: dayjs().format('YYYY-M-D'),
    minDate: dayjs().subtract(5, 'year').format('YYYY-M-D'),
}

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

// Nuevo computed para calcular el monto basado en ítems seleccionados
const calculatedAmount = computed(() => {
    if (!invoice.value.items || payment.paid_items.length === 0) return 0

    // Sumar el total de los ítems seleccionados
    return payment.paid_items.reduce((total, itemId) => {
        const item = invoice.value.items.find(i => i.id === itemId)
        return total + (item ? parseFloat(item.total) : 0)
    }, 0)
})

// Métodos
const loadInvoice = async () => {
    try {
        loading.value = true
        const response = await axios.get(`/api/v2/invoices/${invoiceId}`)
        invoice.value = response.data

        // Resetear items seleccionados y monto
        payment.paid_items = []
        payment.amount = 0

    } catch (error) {
        console.error('Error al cargar factura:', error)
        showMessage('Error al cargar la factura', 'error')
    } finally {
        loading.value = false
    }
}

const updatePaymentAmount = () => {
    // Actualizar el monto basado en los ítems seleccionados
    payment.amount = calculatedAmount.value
}

const submitPayment = async () => {
    if (payment.paid_items.length === 0) {
        showMessage('Debe seleccionar al menos un ítem para pagar', 'warning')
        return
    }

    if (payment.amount <= 0) {
        showMessage('El monto debe ser mayor a 0', 'warning')
        return
    }

    // Validar que el monto no exceda el saldo pendiente
    if (payment.amount > balance.value) {
        showMessage('El monto no puede ser mayor al saldo pendiente', 'warning')
        return
    }

    try {
        paymentLoading.value = true

        const response = await axios.post(`/api/v2/invoices/${invoiceId}/payment`, {
            amount: payment.amount,
            payment_method: payment.payment_method,
            reference: payment.reference,
            payment_date: payment.payment_date,
            notes: payment.notes,
            paid_items: payment.paid_items  // Enviar los ítems que se marcarán como pagados
        })

        // Recargar la factura para actualizar datos
        await loadInvoice()

        // Resetear formulario
        resetPaymentForm()

        showMessage('Pago registrado exitosamente')

    } catch (error) {
        console.error('Error al registrar pago:', error)
        showMessage('Error al registrar el pago', 'error')
    } finally {
        paymentLoading.value = false
    }
}

const resetPaymentForm = () => {
    payment.amount = 0
    payment.reference = ''
    payment.notes = ''
    payment.paid_items = []
    payment.payment_date = new Date().toISOString().split('T')[0]
}

const canDeleteInvoice = (invoice) => {
    // Solo permitir eliminar facturas pendientes o parciales
    return ['pending', 'partial'].includes(invoice.status)
}

const confirmDelete = async () => {

    Swal.fire({
        title: "¿Está seguro de eliminar esta factura?",
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "Sí",
        denyButtonText: `No`,
    }).then(async(result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/v2/invoices/${invoiceId}`)
                router.push('/facturas')
            } catch (error) {
                console.error('Error al eliminar factura:', error)
                showMessage('Error al eliminar la factura', 'error')
            }
        }
    });
}

// Métodos de utilidad
const formatDate = (dateString) => {
    return dayjs(dateString).format('YYYY-M-D')
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
    const classes = {
        'paid': 'Pagado',
        'partial': 'Parcial',
        'pending': 'Pendiente',
        'cancelled': 'Cancelada'
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
        'additional': 'Item'
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

// Watcher para actualizar automáticamente el monto cuando cambian los ítems seleccionados
watch(() => payment.paid_items, () => {
    updatePaymentAmount()
})
</script>

<style scoped>
.badge {
    font-size: 0.8em;
}
</style>