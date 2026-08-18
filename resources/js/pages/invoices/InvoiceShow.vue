<template>
    <div class="layout-px-spacing">
        <ContentState
            v-if="loading"
            type="loading"
            title="Cargando factura"
            message="Estamos consultando el detalle, los conceptos y el historial de pagos."
            class="layout-top-spacing"
        />
        <ContentState
            v-else-if="loadError"
            type="error"
            title="No fue posible cargar la factura"
            :message="loadError"
            action-label="Reintentar"
            class="layout-top-spacing"
            @action="loadInvoice"
        />
        <div v-else class="row layout-top-spacing">

            <div class="col-md-8">
                <!-- Información de la factura -->
                <div class="card mb-4" data-tour="invoice-show-summary">
                    <AppPageHeader
                        class="card-header"
                        :title="`Factura #${invoice.invoice_number}`"
                        subtitle="Detalle financiero, conceptos incluidos e historial de pagos."
                        icon="fa fa-file-invoice"
                    >
                        <template #actions>
                            <AppStatus :value="invoice.status" context="invoice" class="text-uppercase" />
                        </template>
                    </AppPageHeader>
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
                                <p><strong>Fecha Emisión:</strong> <AppDate :value="invoice.issue_date" /></p>
                                <p><strong>Fecha Vencimiento:</strong> <AppDate :value="invoice.due_date" /></p>
                                <p><strong>Creada por:</strong> {{ invoice.creator?.name || 'Sistema' }}</p>
                            </div>
                        </div>

                        <div v-if="invoice.numbering_type === 'electronic' && invoice.number_range" class="alert alert-info">
                            <div class="fw-semibold mb-1">Numeración electrónica autorizada</div>
                            <div>Resolución {{ invoice.number_range.resolution_number }} del {{ invoice.number_range.resolution_date }}</div>
                            <div>
                                Rango {{ invoice.number_range.prefix || '' }}{{ invoice.number_range.range_start }}
                                a {{ invoice.number_range.prefix || '' }}{{ invoice.number_range.range_end }} ·
                                Vigencia {{ invoice.number_range.valid_from }} a {{ invoice.number_range.valid_until }}
                            </div>
                        </div>
                        <div v-else-if="invoice.numbering_type === 'internal'" class="alert alert-secondary py-2">
                            Esta factura utiliza numeración interna de la escuela.
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
                                        <td class="text-right"><AppMoney :value="item.unit_price" /></td>
                                        <td class="text-right"><AppMoney :value="item.total" /></td>
                                        <td class="text-center">
                                            <AppStatus :value="item.is_paid" context="invoice-item" />
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Total Factura:</strong></td>
                                        <td class="text-right">
                                            <strong><AppMoney :value="invoice.total_amount" /></strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Pagado:</strong></td>
                                        <td class="text-right">
                                            <strong><AppMoney :value="invoice.paid_amount" /></strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Saldo Pendiente:</strong></td>
                                        <td class="text-right">
                                            <strong :class="balance > 0 ? 'text-danger' : 'text-success'">
                                                <AppMoney :value="balance" />
                                            </strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Notas -->
                        <div v-if="invoice.notes" class="alert mt-2">
                            <h6><i class="fa fa-sticky-note"></i> Notas:</h6>
                            <p>{{ invoice.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Comprobantes de pago -->
                <div v-if="paymentRequests.length > 0" class="card mb-4" data-tour="invoice-show-payment-requests">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-receipt"></i> Comprobantes de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Enviado en</th>
                                        <th>Método</th>
                                        <th>Referencia</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-center">Comprobante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="paymentRequest in paymentRequests" :key="paymentRequest.id">
                                        <td><AppDate :value="paymentRequest.created_at" /></td>
                                        <td>
                                            <span
                                                :class="`badge badge-${getPaymentMethodClass(paymentRequest.payment_method)}`">
                                                {{ getPaymentMethodLabel(paymentRequest.payment_method) }}
                                            </span>
                                        </td>
                                        <td>{{ paymentRequest.reference_number || 'N/A' }}</td>
                                        <td class="text-right"><AppMoney :value="paymentRequest.amount" /></td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-info btn-sm"
                                                @click="openProofModal(paymentRequest)"
                                            >
                                                <i class="fa fa-image"></i> Ver
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Historial de pagos -->
                <div v-if="invoice.payments?.length > 0" class="card" data-tour="invoice-show-history">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fa fa-history"></i> Historial de Pagos</h5>
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
                                        <td><AppDate :value="payment.payment_date" /></td>
                                        <td>
                                            <span
                                                :class="`badge badge-${getPaymentMethodClass(payment.payment_method)}`">
                                                {{ getPaymentMethodLabel(payment.payment_method) }}
                                            </span>
                                        </td>
                                        <td>{{ payment.reference || 'N/A' }}</td>
                                        <td class="text-right"><AppMoney :value="payment.amount" /></td>
                                        <td>{{ payment.creator?.name || 'Sistema' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de pagos -->
            <div class="col-md-4">
                <div class="card mb-4" data-tour="invoice-show-payment-form">
                    <div class="card-header d-flex justify-content-md-between">
                        <h5 class="mb-0"><i class="fa fa-money-bill-wave"></i> Registrar Pago</h5>
                        <AppButton variant="info" size="sm" @click="tutorial.start()">
                            <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                            Guía
                        </AppButton>
                    </div>
                    <div class="card-body col-md-12">
                        <div v-if="actionError" class="alert alert-danger" role="alert">
                            {{ actionError }}
                        </div>
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
                                                    <span class="font-weight-bold"><AppMoney :value="item.total" /></span>
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

                                <template v-if="unpaidItems.length > 0">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Monto a Pagar <span class="text-danger">&nbsp;(*)</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm"
                                                    :value="formatAppMoney(calculatedAmount)" disabled
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
                                            <label>Fecha de Emisión <span class="text-danger">&nbsp;(*)</span></label>
                                            <flat-pickr
                                                :config="flatpickrConfig"
                                                class="form-control form-control-sm flatpickr"
                                                id="invoiceIssueDate"
                                                v-model="payment.issue_date"
                                                :disabled="invoice.numbering_type === 'electronic'"
                                                required
                                                v-tooltip.top="invoice.numbering_type === 'electronic' ? 'La fecha de una factura electrónica es inmutable' : 'Puedes cambiar la fecha de emisión de la factura'"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                        <div class="form-group">
                                            <label>Fecha del Pago <span class="text-danger">&nbsp;(*)</span></label>
                                            <flat-pickr
                                                :config="flatpickrConfig"
                                                class="form-control form-control-sm flatpickr"
                                                id="invoicePaymentDate"
                                                v-model="payment.payment_date"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Notas</label>
                                            <textarea class="form-control form-control-sm" v-model="payment.notes" rows="2"></textarea>
                                        </div>
                                    </div> -->
                                </template>

                                <div class="btn-group" data-tour="invoice-show-actions">

                                    <button type="submit" class="btn btn-success btn-block" v-if="unpaidItems.length > 0"
                                        :disabled="paymentLoading || calculatedAmount <= 0 || payment.paid_items.length === 0">
                                        <span v-if="paymentLoading" class="spinner-border spinner-border-sm"></span>
                                        <i v-else class="fas fa-check-circle"></i>
                                        {{ paymentLoading ? 'Registrando...' : `Pagar
                                        ${formatAppMoney(calculatedAmount)}` }}
                                    </button>

                                    <a :href="invoice.url_print" class="btn btn-info btn-block " target="_blank">
                                        <i class="fa fa-print"></i> Imprimir
                                    </a>
                                    <button v-if="canDeleteInvoice(invoice)" type="button" @click="confirmDelete"
                                        class="btn btn-outline-danger btn-block" :disabled="deleteLoading">
                                        <span v-if="deleteLoading" class="spinner-border spinner-border-sm"></span>
                                        <i v-else class="fa fa-ban"></i>
                                        {{ deleteLoading ? 'Anulando...' : 'Anular factura' }}
                                    </button>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <PageTutorialOverlay :tutorial="tutorial" />

    <div ref="proofModalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Referencia: {{ selectedProof.title || 'Comprobante' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img :src="selectedProof.url" :alt="selectedProof.title || 'Comprobante de pago'"
                        class="img-fluid rounded proof-image" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, reactive, watch } from 'vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import ContentState from '@/components/general/ContentState.vue'
import AppDate from '@/components/general/AppDate.vue'
import AppMoney from '@/components/general/AppMoney.vue'
import AppStatus from '@/components/general/AppStatus.vue'
import AppButton from '@/components/general/AppButton.vue'
import AppPageHeader from '@/components/general/AppPageHeader.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { useRoute, useRouter } from 'vue-router'
import api from '@/utils/axios'
import dayjs from '@/utils/dayjs';
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";
import { invoiceShowTutorial } from '@/tutorials/invoices'
import { formatAppMoney } from '@/utils/appFormatters'

const flatpickrConfig = {
    wrap: true,
    locale: Spanish,
    maxDate: dayjs().format('YYYY-M-D'),
    minDate: dayjs().subtract(5, 'year').format('YYYY-M-D'),
}

const route = useRoute()
const router = useRouter()
const invoiceId = route.params.id
const tutorial = usePageTutorial(invoiceShowTutorial)
const todayDate = dayjs().format('YYYY-MM-DD')

// Estado reactivo
const invoice = ref({ items: [], payments: [] })
const loading = ref(true)
const loadError = ref('')
const actionError = ref('')
const paymentLoading = ref(false)
const deleteLoading = ref(false)
const proofModalElement = ref(null)
const selectedProof = ref({ url: '', title: '' })

let proofModalInstance = null

const createIdempotencyKey = (prefix) => {
    const randomValue = globalThis.crypto?.randomUUID?.()
        ?? `${Date.now()}-${Math.random().toString(16).slice(2)}`

    return `${prefix}-${invoiceId}-${randomValue}`.slice(0, 64)
}

const paymentIdempotencyKey = ref(createIdempotencyKey('invoice-payment'))

// Formulario de pago
const payment = reactive({
    amount: 0,
    payment_method: 'cash',
    reference: '',
    issue_date: todayDate,
    payment_date: todayDate,
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

const paymentRequests = computed(() => {
    return invoice.value.payment_requests || invoice.value.paymentRequests || []
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
const loadInvoice = async (showLoader = true) => {
    try {
        if (showLoader) {
            loading.value = true
        }
        loadError.value = ''
        const response = await api.get(`/api/v2/invoices/${invoiceId}`)
        invoice.value = response.data

        // Resetear items seleccionados y monto
        payment.paid_items = []
        payment.amount = 0
        payment.issue_date = toPickerDate(invoice.value.issue_date)
        payment.payment_date = todayDate

    } catch (error) {
        console.error('Error al cargar factura:', error)
        const message = error.response?.data?.message || 'No fue posible cargar la factura. Intenta nuevamente.'

        if (showLoader) {
            loadError.value = message
        } else {
            actionError.value = message
        }
    } finally {
        if (showLoader) {
            loading.value = false
        }
    }
}

const updatePaymentAmount = () => {
    // Actualizar el monto basado en los ítems seleccionados
    payment.amount = calculatedAmount.value
}

const submitPayment = async () => {
    if (paymentLoading.value) {
        return
    }

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
        actionError.value = ''

        const response = await api.post(`/api/v2/invoices/${invoiceId}/payment`, {
            idempotency_key: paymentIdempotencyKey.value,
            amount: payment.amount,
            payment_method: payment.payment_method,
            issue_date: payment.issue_date,
            reference: payment.reference,
            payment_date: payment.payment_date,
            notes: payment.notes,
            paid_items: payment.paid_items  // Enviar los ítems que se marcarán como pagados
        })

        // Recargar la factura para actualizar datos
        await loadInvoice(false)

        // Resetear formulario
        resetPaymentForm()

        paymentIdempotencyKey.value = createIdempotencyKey('invoice-payment')
        showMessage(response.data.created === false ? 'El pago ya estaba registrado.' : 'Pago registrado exitosamente')

    } catch (error) {
        console.error('Error al registrar pago:', error)
        actionError.value = error.response?.data?.message || 'No fue posible registrar el pago. Revisa los datos e intenta nuevamente.'
    } finally {
        paymentLoading.value = false
    }
}

const resetPaymentForm = () => {
    payment.amount = 0
    payment.reference = ''
    payment.notes = ''
    payment.paid_items = []
    payment.issue_date = toPickerDate(invoice.value.issue_date)
    payment.payment_date = todayDate
}

const canDeleteInvoice = (invoice) => {
    // Solo permitir eliminar facturas pendientes o parciales
    return ['pending', 'partial'].includes(invoice.status)
}

const confirmDelete = async () => {
    if (deleteLoading.value) {
        return
    }

    const result = await Swal.fire({
        title: `¿Anular la factura #${invoice.value.invoice_number}?`,
        text: 'La factura dejará de estar disponible para la operación diaria. Esta acción no registra un pago.',
        icon: 'warning',
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: 'Sí, anular factura',
        cancelButtonText: 'Conservar factura',
        focusCancel: true,
    })

    if (!result.isConfirmed) {
        return
    }

    try {
        deleteLoading.value = true
        actionError.value = ''
        await api.delete(`/api/v2/invoices/${invoiceId}`)
        router.push('/facturas')
    } catch (error) {
        console.error('Error al anular factura:', error)
        actionError.value = error.response?.data?.message || 'No fue posible anular la factura. Intenta nuevamente.'
    } finally {
        deleteLoading.value = false
    }
}

const openProofModal = (paymentRequest) => {
    selectedProof.value = {
        url: paymentRequest.url_image,
        title: paymentRequest.reference_number,
    }

    proofModalInstance?.show()
}

// Métodos de utilidad
const toPickerDate = (dateValue) => {
    if (!dateValue) {
        return todayDate
    }

    return dayjs(dateValue).format('YYYY-MM-DD')
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
    if (proofModalElement.value) {
        proofModalInstance = new window.bootstrap.Modal(proofModalElement.value)
    }

    loadInvoice()
})

onBeforeUnmount(() => {
    proofModalInstance?.dispose()
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

.proof-image {
    max-height: 70vh;
    object-fit: contain;
}
</style>
