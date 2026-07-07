<template>
    <panel>
        <template #body>
            <Loader :is-loading="isLoading" loading-text="Cargando resumen..." />

            <div v-if="summary" class="inscription-summary">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <img :src="player.photo_url || '/img/user.webp'" alt="avatar" class="player-avatar-lg">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h4 class="mb-0">{{ player.full_names }}</h4>
                                <span class="badge bg-primary">{{ inscription.unique_code }}</span>
                                <span class="badge" :class="summary.can_edit ? 'bg-success' : 'bg-secondary'">
                                    {{ summary.can_edit ? 'Editable' : 'Sólo lectura' }}
                                </span>
                                <span class="badge" :class="inscription.status === 'active' ? 'bg-success' : 'bg-warning text-dark'">
                                    {{ inscription.status_label }}
                                </span>
                            </div>
                            <div class="text-muted small">
                                Año {{ inscription.year }} · {{ inscription.category || 'Sin categoría' }} ·
                                {{ inscription.training_group?.full_group || inscription.training_group?.name || 'Sin grupo' }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-start justify-content-xl-end">
                        <select
                            v-if="summary.years?.length"
                            class="form-select form-select-sm summary-year-select"
                            :value="String(inscription.id)"
                            @change="goToInscription($event.target.value)"
                        >
                            <option v-for="year in summary.years" :key="year.id" :value="String(year.id)">
                                {{ year.year }} · {{ year.status_label }}
                            </option>
                        </select>
                        <router-link :to="{ name: 'inscriptions' }" class="btn btn-secondary btn-sm">
                            Volver
                        </router-link>
                        <router-link :to="{ name: 'player-stats.detail', params: { id: player.id } }" class="btn btn-primary btn-sm">
                            Estadísticas
                        </router-link>
                        <button
                            type="button"
                            class="btn btn-success btn-sm"
                            :disabled="isGeneratingClearance"
                            @click="generateFinancialClearance(player.unique_code || inscription.unique_code)"
                        >
                            <span v-if="isGeneratingClearance" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                            <i v-else class="fa-solid fa-file-circle-check me-2"></i>
                            {{ isGeneratingClearance ? 'Verificando...' : 'Generar paz y salvo' }}
                        </button>
                        <a :href="summary.links.print" target="_blank" rel="noopener" class="btn btn-dark btn-sm">
                            PDF inscripción
                        </a>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3">
                    <li v-for="tab in tabs" :key="tab.key" class="nav-item">
                        <button
                            type="button"
                            class="nav-link"
                            :class="{ active: activeTab === tab.key }"
                            @click="activeTab = tab.key"
                        >
                            {{ tab.label }}
                        </button>
                    </li>
                </ul>

                <div v-if="activeTab === 'summary'" class="row g-3">
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Datos del deportista</h6>
                                <dl class="summary-list">
                                    <dt>Documento</dt><dd>{{ player.identification_document || '—' }}</dd>
                                    <dt>Género</dt><dd>{{ player.gender || '—' }}</dd>
                                    <dt>Nacimiento</dt><dd>{{ player.date_birth || '—' }}</dd>
                                    <dt>EPS</dt><dd>{{ player.eps || '—' }}</dd>
                                    <dt>RH</dt><dd>{{ player.rh || '—' }}</dd>
                                    <dt>Teléfono</dt><dd>{{ player.mobile || player.phones || '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Inscripción</h6>
                                <dl class="summary-list">
                                    <dt>Inicio</dt><dd>{{ dayjs(inscription.start_date).format('YYYY-M-D') || '—' }}</dd>
                                    <dt>Grupo</dt><dd>{{ inscription.training_group?.name || '—' }}</dd>
                                    <dt>Preinscripción</dt><dd>{{ yesNo(inscription.pre_inscription) }}</dd>
                                    <dt>Hermano</dt><dd>{{ yesNo(inscription.brother_payment) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Documentos</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span v-for="doc in documents" :key="doc.key" class="badge" :class="doc.value ? 'bg-success' : 'bg-secondary'">
                                        {{ doc.label }}: {{ yesNo(doc.value) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'payments'" class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th v-for="field in paymentFields" :key="field.key">{{ field.short }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in payments" :key="payment.id">
                                <td>{{ payment.year }}</td>
                                <td v-for="field in paymentFields" :key="`${payment.id}-${field.key}`">
                                    <template v-if="isEditingPayment(payment, field.key)">
                                        <select v-model="payment[field.key]" class="form-select form-select-sm mb-1">
                                            <option v-for="type in paymentTypes" :key="type.value" :value="Number(type.value)">
                                                {{ type.label }}
                                            </option>
                                        </select>
                                        <CurrencyInput v-model="payment[`${field.key}_amount`]" class="form-control form-control-sm mb-1" />
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-success btn-sm" @click="savePayment(payment, field.key)">
                                                Guardar
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" @click="cancelPaymentEdit">
                                                Cancelar
                                            </button>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="badge" :class="`payments-c-${payment[field.key]}`">
                                            {{ paymentTypeLabels[String(payment[field.key])] || payment[field.key] }}
                                        </span>
                                        <div class="small text-muted">{{ formatMoney(payment[`${field.key}_amount`]) }}</div>
                                        <button
                                            v-if="summary.can_edit && canEditPayment(payment, field.key)"
                                            type="button"
                                            class="btn btn-link btn-sm p-0"
                                            @click="editPayment(payment, field.key)"
                                        >
                                           <i class="far fa-edit"></i>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="!payments.length">
                                <td :colspan="paymentFields.length + 1" class="text-muted">No hay mensualidades registradas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="activeTab === 'attendance'" class="d-flex flex-column gap-3">
                    <div v-if="attendance.length" class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
                        <div>
                            <label for="attendance_month" class="form-label mb-1">Mes</label>
                            <select
                                id="attendance_month"
                                v-model="selectedAttendanceMonth"
                                class="form-select form-select-sm summary-month-select"
                            >
                                <option
                                    v-for="option in attendanceMonthOptions"
                                    :key="option.value"
                                    :value="String(option.value)"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <span class="text-muted small">
                            {{ filteredAttendance.length ? 'Mostrando asistencias del mes seleccionado.' : 'No hay asistencias para el mes seleccionado.' }}
                        </span>
                    </div>

                    <div v-for="assist in filteredAttendance" :key="assist.id" class="card">
                        <div class="card-body">
                            <h6 class="mb-3">{{ assist.month_label }} {{ assist.year }}</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Clase</th>
                                            <th>Fecha</th>
                                            <th>Asistencia</th>
                                            <th>Observación</th>
                                            <th v-if="summary.can_edit" class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="register in assist.registers" :key="`${assist.id}-${register.column}`">
                                            <td>#{{ register.class_number }}</td>
                                            <td>{{ register.day }} {{ register.date }}</td>
                                            <td>
                                                <select
                                                    v-if="summary.can_edit"
                                                    v-model="register.value"
                                                    class="form-select form-select-sm"
                                                >
                                                    <option value="">Selecciona...</option>
                                                    <option v-for="(label, value) in attendanceTypes" :key="value" :value="Number(value)">
                                                        {{ label }}
                                                    </option>
                                                </select>
                                                <span v-else v-html=" register.label || '—'"></span>
                                            </td>
                                            <td>
                                                <textarea
                                                    v-if="summary.can_edit"
                                                    v-model="register.observation"
                                                    class="form-control form-control-sm"
                                                    rows="1"
                                                />
                                                <span v-else>{{ register.observation || '—' }}</span>
                                            </td>
                                            <td v-if="summary.can_edit" class="text-end">
                                                <button type="button" class="btn btn-primary btn-sm" @click="saveAttendance(assist, register)">
                                                    Guardar
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="!assist.registers.length">
                                            <td :colspan="summary.can_edit ? 5 : 4" class="text-muted">No hay clases configuradas para este mes.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-if="!attendance.length" class="text-muted">No hay asistencias registradas.</div>
                    <div v-else-if="!filteredAttendance.length" class="text-muted">No hay asistencias registradas para el mes seleccionado.</div>
                </div>

                <div v-if="activeTab === 'invoices'" class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Pagado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="invoice in invoices" :key="invoice.id">
                                <td>{{ invoice.invoice_number }}</td>
                                <td>{{ invoice.issue_date || '—' }}</td>
                                <td>{{ invoiceStatusLabel(invoice.status) }}</td>
                                <td>{{ formatMoney(invoice.total_amount) }}</td>
                                <td>{{ formatMoney(invoice.paid_amount) }}</td>
                                <td class="text-end">
                                    <router-link :to="{ name: 'invoices.show', params: { id: invoice.id } }" class="btn btn-primary btn-sm me-1">
                                        Ver
                                    </router-link>
                                    <a :href="invoice.url_print" target="_blank" rel="noopener" class="btn btn-dark btn-sm">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!invoices.length">
                                <td colspan="6" class="text-muted">No hay facturas para esta inscripción.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="activeTab === 'evaluations'" class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Plantilla</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Nota</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="evaluation in evaluations" :key="evaluation.id">
                                <td>{{ evaluation.period?.name || '—' }}</td>
                                <td>{{ evaluation.template?.name || '—' }}</td>
                                <td>{{ evaluation.evaluation_type || '—' }}</td>
                                <td>{{ evaluation.status || '—' }}</td>
                                <td>{{ evaluation.overall_score ?? '—' }}</td>
                                <td class="text-end">
                                    <router-link :to="{ name: 'player-evaluations.show', params: { id: evaluation.id } }" class="btn btn-primary btn-sm me-1">
                                        Ver
                                    </router-link>
                                    <a :href="evaluation.urls?.pdf" target="_blank" rel="noopener" class="btn btn-dark btn-sm">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!evaluations.length">
                                <td colspan="6" class="text-muted">No hay evaluaciones para esta inscripción.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Inscripciones'" :current="'Resumen'" />
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import cloneDeep from 'lodash.clonedeep'
import api from '@/utils/axios'
import Loader from '@/components/general/Loader.vue'
import CurrencyInput from '@/components/general/CurrencyInput.vue'
import { useSetting } from '@/store/settings-store'
import { usePageTitle } from '@/composables/use-meta'
import useFinancialClearance from '@/composables/player/useFinancialClearance'
import dayjs from "@/utils/dayjs";

usePageTitle('Resumen de inscripción')

const route = useRoute()
const router = useRouter()
const settings = useSetting()
const { isGeneratingClearance, generateFinancialClearance } = useFinancialClearance()

const summary = ref(null)
const isLoading = ref(false)
const activeTab = ref('summary')
const editingPayment = ref(null)
const paymentBackup = ref(null)
const selectedAttendanceMonth = ref('')

const baseTabs = [
    { key: 'summary', label: 'Resumen' },
    { key: 'payments', label: 'Pagos' },
    { key: 'attendance', label: 'Asistencias' },
    { key: 'invoices', label: 'Facturas' },
    { key: 'evaluations', label: 'Evaluaciones' },
]

const paymentFields = [
    { key: 'enrollment', short: 'Mat.' },
    { key: 'january', short: 'Ene' },
    { key: 'february', short: 'Feb' },
    { key: 'march', short: 'Mar' },
    { key: 'april', short: 'Abr' },
    { key: 'may', short: 'May' },
    { key: 'june', short: 'Jun' },
    { key: 'july', short: 'Jul' },
    { key: 'august', short: 'Ago' },
    { key: 'september', short: 'Sep' },
    { key: 'october', short: 'Oct' },
    { key: 'november', short: 'Nov' },
    { key: 'december', short: 'Dic' },
]

const attendanceTypes = {
    1: 'Asistencia',
    2: 'Falta',
    3: 'Excusa',
    4: 'Retiro',
    5: 'Incapacidad',
}

const invoiceStatusLabels = {
    paid: 'Pagada',
    partial: 'Parcial',
    pending: 'Pendiente',
    cancelled: 'Cancelada',
}

const readOnlyPaymentTypes = [14]

const inscription = computed(() => summary.value?.inscription || {})
const player = computed(() => summary.value?.player || {})
const payments = computed(() => summary.value?.payments || [])
const attendance = computed(() => summary.value?.attendance || [])
const attendanceMonthOptions = computed(() => attendance.value.map((assist) => ({
    value: assist.month,
    label: `${assist.month_label} ${assist.year}`,
})))
const filteredAttendance = computed(() => {
    if (!selectedAttendanceMonth.value) {
        return attendance.value.slice(0, 1)
    }

    return attendance.value.filter((assist) => String(assist.month) === String(selectedAttendanceMonth.value))
})
const invoices = computed(() => summary.value?.invoices || [])
const evaluations = computed(() => summary.value?.evaluations || [])
const tabs = computed(() => baseTabs.filter((tab) => {
    if (tab.key === 'invoices') {
        return invoices.value.length > 0
    }

    if (tab.key === 'evaluations') {
        return evaluations.value.length > 0
    }

    return true
}))
const paymentTypes = computed(() => settings.paymentTypeOptions || [])
const paymentTypeLabels = computed(() => settings.paymentTypeLabels || {})
const documents = computed(() => {
    const documentMap = [
        ['photos', 'Fotos'],
        ['copy_identification_document', 'Doc. identidad'],
        ['eps_certificate', 'Cert. EPS'],
        ['medic_certificate', 'Cert. médico'],
        ['study_certificate', 'Cert. estudio'],
    ]

    return documentMap.map(([key, label]) => ({
        key,
        label,
        value: Boolean(inscription.value.documents?.[key]),
    }))
})

function notify(message, type = 'success') {
    if (typeof showMessage === 'function') {
        showMessage(message, type)
    }
}

function yesNo(value) {
    return value ? 'Sí' : 'No'
}

function formatMoney(value) {
    if (typeof moneyFormat === 'function') {
        return moneyFormat(value || 0)
    }

    return Number(value || 0).toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    })
}

function invoiceStatusLabel(status) {
    return invoiceStatusLabels[status] || status || '—'
}

function canEditPayment(payment, field) {
    return !payment.inscription_deleted && !readOnlyPaymentTypes.includes(Number(payment[field]))
}

function isEditingPayment(payment, field) {
    return editingPayment.value?.payment?.id === payment.id && editingPayment.value?.field === field
}

function editPayment(payment, field) {
    paymentBackup.value = cloneDeep(payment)
    editingPayment.value = { payment, field }
}

function cancelPaymentEdit() {
    if (paymentBackup.value && editingPayment.value?.payment) {
        Object.assign(editingPayment.value.payment, paymentBackup.value)
    }
    editingPayment.value = null
    paymentBackup.value = null
}

function saveErrorMessage(error, fallback) {
    const backendErrors = error.response?.data?.errors
    const firstError = backendErrors ? Object.values(backendErrors).flat()[0] : null

    return firstError || error.response?.data?.message || fallback
}

async function savePayment(payment, field) {
    const amountField = `${field}_amount`

    try {
        isLoading.value = true
        const { data } = await api.post(`/api/v2/payments/${payment.id}`, {
            _method: 'PUT',
            column: field,
            [field]: payment[field],
            [amountField]: payment[amountField],
        })

        if (data?.data) {
            Object.assign(payment, data.data)
        }
        editingPayment.value = null
        paymentBackup.value = null
        notify('Mensualidad guardada correctamente')
    } catch (error) {
        notify(saveErrorMessage(error, 'No fue posible guardar la mensualidad.'), 'error')
    } finally {
        isLoading.value = false
    }
}

async function saveAttendance(assist, register) {
    try {
        isLoading.value = true
        const payload = {
            _method: 'PUT',
            id: assist.id,
            observations: register.observation || '',
            attendance_date: register.attendance_date,
            [register.column]: register.value || null,
        }

        await api.post(`/api/v2/assists/${assist.id}`, payload)
        register.label = attendanceTypes[register.value] || ''
        notify('Asistencia guardada correctamente')
    } catch (error) {
        notify(saveErrorMessage(error, 'No fue posible guardar la asistencia.'), 'error')
    } finally {
        isLoading.value = false
    }
}

async function loadSummary() {
    try {
        isLoading.value = true
        const { data } = await api.get(`/api/v2/inscriptions/${route.params.id}/summary`)
        summary.value = data.data
        selectedAttendanceMonth.value = String(data.data?.attendance?.[0]?.month || '')
    } catch (error) {
        summary.value = null
        selectedAttendanceMonth.value = ''
        notify(saveErrorMessage(error, 'No fue posible cargar el resumen de inscripción.'), 'error')
    } finally {
        isLoading.value = false
    }
}

function goToInscription(id) {
    router.push({ name: 'inscriptions.summary', params: { id } })
}

watch(tabs, (visibleTabs) => {
    if (!visibleTabs.some((tab) => tab.key === activeTab.value)) {
        activeTab.value = 'summary'
    }
})

watch(() => route.params.id, () => {
    activeTab.value = 'summary'
    editingPayment.value = null
    paymentBackup.value = null
    loadSummary()
})

onMounted(async () => {
    await settings.getSettings()
    await loadSummary()
})
</script>

<style scoped>
.inscription-summary {
    position: relative;
}

.player-avatar-lg {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
}

.summary-year-select {
    min-width: 180px;
}

.summary-month-select {
    min-width: 180px;
}

.summary-list {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: .35rem .75rem;
    margin-bottom: 0;
}

.summary-list dt {
    color: var(--bs-secondary-color);
    font-weight: 600;
}

.summary-list dd {
    margin-bottom: 0;
}
</style>
