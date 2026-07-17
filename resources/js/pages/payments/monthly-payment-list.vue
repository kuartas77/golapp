<template>
    <panel>
        <template #body>

            <div class="row" data-tour="monthly-payments-filters">

                <div class="col-xl-6 col-lg-6 col-sm-12 text-center">
                    <Form ref="form" :validation-schema="schema" @submit="handleSearch" :initial-values="formData"
                        class="row align-items-center justify-content-center">
                        <p class="text-muted">
                            Para el año actual selecciona al menos un grupo o una categoría; también puedes combinar ambos.
                            En años anteriores puedes consultar sólo por año o aplicar esos mismos filtros.
                        </p>
                        <div class="col-sm-3">
                            <label for="training_group_id" class="sr-only">Grupo</label>
                            <Field name="training_group_id" as="CustomSelect2" :options="groups" id="training_group_id"
                                placeholder="Grupo" />
                            <ErrorMessage name="training_group_id" class="custom-error" />
                        </div>
                        <div class="col-sm-3">
                            <label for="category" class="sr-only">Categoría</label>
                            <Field name="category" as="CustomSelect2" :options="categories" id="category"
                                placeholder="Categoría" />
                            <ErrorMessage name="category" class="custom-error" />
                        </div>
                        <div class="col-sm-2">
                            <label for="year" class="sr-only">Año</label>
                            <Field name="year" as="CustomSelect2" :options="years" id="year"
                                placeholder="Año" />
                            <ErrorMessage name="year" class="custom-error" />
                        </div>
                        <div class="col-sm-3 mt-2">
                            <label for="month" class="sr-only">Mes</label>
                            <Field name="month" as="CustomSelect2" :options="monthOptions" id="month"
                                placeholder="Mes" />
                            <ErrorMessage name="month" class="custom-error" />
                        </div>
                        <div class="col-sm-3 mt-2">
                            <label for="status" class="sr-only">Estado</label>
                            <Field name="status" as="CustomSelect2" :options="statusOptions" id="status"
                                placeholder="Estado" />
                            <ErrorMessage name="status" class="custom-error" />
                        </div>
                        <div class="col-sm-3 mt-2">
                            <label for="player_name" class="sr-only">Deportista</label>
                            <Field name="player_name" id="player_name" class="form-control form-control-sm"
                                placeholder="Deportista" />
                            <ErrorMessage name="player_name" class="custom-error" />
                        </div>
                        <div class="col-sm-3 mt-2">
                            <label for="unique_code" class="sr-only">Código</label>
                            <Field name="unique_code" id="unique_code" class="form-control form-control-sm"
                                placeholder="Código" />
                            <ErrorMessage name="unique_code" class="custom-error" />
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary w-100" :disabled="isLoading">
                                Buscar
                                <template v-if="isLoading">
                                    &nbsp;
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-loader spin me-2">
                                        <line x1="12" y1="2" x2="12" y2="6"></line>
                                        <line x1="12" y1="18" x2="12" y2="22"></line>
                                        <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                        <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                        <line x1="2" y1="12" x2="6" y2="12"></line>
                                        <line x1="18" y1="12" x2="22" y2="12"></line>
                                        <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                        <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                    </svg>
                                </template>
                            </button>
                        </div>
                    </Form>
                </div>

                <div class="col-xl-6 col-lg-6 col-sm-12 text-center" data-tour="monthly-payments-summary">
                    <div class="row">

                        <div class="col-12 mt-1">

                            <span class="badge outline-badge-info me-1">
                                Total página {{ moneyFormat(totalByType.total) }}
                            </span>
                            <span class="badge outline-badge-info me-1">
                                Pagos {{ moneyFormat(totalByType.pay) }}
                            </span>
                            <span class="badge outline-badge-info me-1">
                                Efectivo {{ moneyFormat(totalByType.cash) }}
                            </span>
                            <span class="badge outline-badge-info me-1">
                                Consignación {{ moneyFormat(totalByType.consignment) }}
                            </span>
                            <span class="badge outline-badge-info me-1">
                                Saldo a favor {{ moneyFormat(totalByType.playerCredit) }}
                            </span>
                            <span class="badge outline-badge-danger  me-1">
                                Deben {{ moneyFormat(totalByType.debts) }}
                            </span>
                            <span class="badge outline-badge-danger me-1">
                                Deudores {{ debtorCount }}
                            </span>
                            <span class="badge outline-badge-success me-1">
                                Recibos {{ receiptableCount }}
                            </span>
                            <!-- <span class="badge outline-badge-info  me-1">
                                Otros {{ moneyFormat(totalByType.others) }}
                            </span> -->



                        </div>

                        <div class="col-12 mt-1">
                            <router-link :to="{ name: 'payments.receipts' }"
                                class="btn btn-primary btn-sm me-1">
                                <i class="far fa-file-pdf me-1"></i>Recibos
                            </router-link>
                            <a v-if="export_pdf" :href="export_pdf" target="_blank"
                                class="badge badge-info btn btn-sm me-1" @click="exportFile($event)">
                                <i class="far fa-file-pdf fa-lg"></i>PDF
                            </a>
                            <a v-if="export_excel" :href="export_excel" target="_blank"
                                class="badge badge-info btn btn-sm me-1" @click="exportFile($event)">
                                <i class="far fa-file-excel fa-lg"></i>EXCEL
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <hr class="bg-primary border-2 border-top border-primary" />

            <div class="row mt-2 justify-content-between">

                <div
                    class="d-md-flex justify-content-between align-items-center dt-layout-start col-md-auto me-auto mb-2">
                    <div class="dt-info">
                        <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                            <i class="fa-regular fa-circle-question me-2"></i>
                            Guía
                        </button>
                    </div>
                </div>

                <div v-if="groupPayments.length" class="col-md-auto mb-2">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Vista de mensualidades">
                        <button type="button" class="btn" :class="viewMode === 'annual' ? 'btn-primary' : 'btn-outline-primary'"
                            @click="viewMode = 'annual'">
                            Anual
                        </button>
                        <button type="button" class="btn" :class="viewMode === 'monthly' ? 'btn-primary' : 'btn-outline-primary'"
                            @click="viewMode = 'monthly'">
                            Mensual
                        </button>
                    </div>
                </div>

                <div v-if="groupPayments.length" class="col-md-4 col-lg-3 mb-2">
                    <label for="player_search" class="sr-only">Buscar por nombre de deportista</label>
                    <input
                        id="player_search"
                        v-model="playerSearchTerm"
                        type="search"
                        class="form-control form-control-sm"
                        placeholder="Buscar por nombre de deportista"
                        autocomplete="off"
                    />
                </div>

                <div
                    class="d-md-flex justify-content-between align-items-center dt-layout-end col-md-auto mb-2">
                    <div class="dt-info">
                        Mostrando {{ visiblePlayerCount }}<template v-if="playerSearchTerm.trim()"> de {{ player_count }}</template> Deportistas.
                    </div>
                </div>

                <div v-if="groupPayments.length" class="col-12">
                    <div class="row align-items-end g-2 justify-content-end">
                        <div class="col-sm-3 col-lg-2">
                            <label for="bulk_status" class="form-label mb-1">Acción masiva</label>
                            <select id="bulk_status" v-model="bulkStatus" class="form-select form-select-sm">
                                <option value="">Estado</option>
                                <option v-for="status in bulkStatusOptions" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-3 col-lg-2">
                            <label for="bulk_amount" class="form-label mb-1">Monto</label>
                            <CurrencyInput
                                id="bulk_amount"
                                v-model="bulkAmount"
                                class="form-control form-control-sm"
                                placeholder="Monto"
                            />
                        </div>
                        <div class="col-sm-4 col-lg-auto">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm w-100"
                                :disabled="isBulkUpdating || !bulkStatus || !bulkEligibleRows.length"
                                @click="applyBulkPaymentStatus"
                            >
                                Aplicar a {{ bulkEligibleRows.length }} visible(s)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="retiredRowsCount" class="alert alert-warning py-2" role="alert">
                Hay {{ retiredRowsCount }} fila(s) con inscripción retirada. Se muestran solo como referencia histórica y permanecen en solo lectura.
            </div>

            <div v-if="viewMode === 'monthly' && filteredGroupPayments.length"
                class="table-responsive"
                data-tour="monthly-payments-table"
            >
                <table class="table table-bordered table-sm dataTable align-middle text-center">
                    <thead>
                        <tr>
                            <th class="dt-head-center dt-body-center">Deportista</th>
                            <th class="dt-head-center dt-body-center">Mes</th>
                            <th class="dt-head-center dt-body-center">Estado</th>
                            <th class="dt-head-center dt-body-center">Monto</th>
                            <th class="dt-head-center dt-body-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in monthlyRows" :key="`${row.payPlayer.id}-${row.field}`">
                            <td class="dt-head-center dt-body-center text-sm-start">
                                <small>
                                    {{ row.payPlayer.player.full_names }}
                                    <span v-if="row.payPlayer.inscription_deleted" class="badge bg-warning text-dark ms-2">
                                        Inscripción retirada
                                    </span>
                                </small>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        {{ row.payPlayer.player.unique_code }}
                                        <span>| {{ row.payPlayer.player.category }}</span>
                                    </small>
                                </p>
                            </td>
                            <td class="dt-head-center dt-body-center">{{ selectedMonthLabel }}</td>
                            <td class="dt-head-center dt-body-center"
                                :data-payment-id="row.payPlayer.id"
                                :data-payment-field="row.field">
                                <template
                                    v-if="editingCell?.payPlayer === row.payPlayer && editingCell?.field === row.field && canEditPaymentRow(row.payPlayer, row.field)">
                                    <select v-model="row.payPlayer[row.field]"
                                        :id="`select_${row.field}_${row.payPlayer.id}`"
                                        :name="`select_${row.field}_${row.payPlayer.id}`" autocomplete="off"
                                        @change="handleSelectChange(row.payPlayer, row.field)"
                                        class="form-select form-select-sm">
                                        <option v-for="type in type_payments" :key="type.value"
                                            :value="type.value">{{ type.label }}</option>
                                    </select>
                                </template>
                                <span v-else :class="`badge payments-c-${row.status}`">
                                    {{ row.statusLabel }}
                                </span>
                            </td>
                            <td class="dt-head-center dt-body-center">
                                <template
                                    v-if="editingCell?.payPlayer === row.payPlayer && editingCell?.field === row.field && canEditPaymentRow(row.payPlayer, row.field)">
                                    <CurrencyInput class="form-control form-control-sm"
                                        v-model="row.payPlayer[`${row.field}_amount`]"
                                        :id="`input_${row.field}_${row.payPlayer.id}`"
                                        :name="`input_${row.field}_${row.payPlayer.id}`" autocomplete="off"
                                        placeholder="Monto" />
                                </template>
                                <small v-else class="text-muted">{{ moneyFormat(row.amount) }}</small>
                            </td>
                            <td class="dt-head-center dt-body-center">
                                <template
                                    v-if="editingCell?.payPlayer === row.payPlayer && editingCell?.field === row.field && canEditPaymentRow(row.payPlayer, row.field)">
                                    <span class="badge badge-success clickable me-1" @click="saveField" title="Guardar">
                                        <i class="far fa-check-square fa-lg"></i>
                                    </span>
                                    <span class="badge badge-info clickable" @click="cancelEdition(row.field)" title="Cancelar">
                                        <i class="far fa-window-close fa-lg"></i>
                                    </span>
                                </template>
                                <span v-else-if="canEditPaymentRow(row.payPlayer, row.field)"
                                    class="badge badge-light btn btn-sm clickable"
                                    @click="editRow(row.payPlayer, row.field)">
                                    <i class="far fa-edit"></i>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else
                ref="scrollContainer"
                class="table-responsive"
                :class="filteredGroupPayments.length ? 'scroll-container' : ''"
                data-tour="monthly-payments-table"
            >
                <table class="table table-bordered table-sm dataTable align-middle text-center">
                    <thead class="">
                        <tr>
                            <th class="dt-head-center dt-body-center">Nombre</th>
                            <th class="dt-head-center dt-body-center">Matrícula</th>
                            <th class="dt-head-center dt-body-center">Ene</th>
                            <th class="dt-head-center dt-body-center">Feb</th>
                            <th class="dt-head-center dt-body-center">Mar</th>
                            <th class="dt-head-center dt-body-center">Abr</th>
                            <th class="dt-head-center dt-body-center">May</th>
                            <th class="dt-head-center dt-body-center">Jun</th>
                            <th class="dt-head-center dt-body-center">Jul</th>
                            <th class="dt-head-center dt-body-center">Ago</th>
                            <th class="dt-head-center dt-body-center">Sep</th>
                            <th class="dt-head-center dt-body-center">Oct</th>
                            <th class="dt-head-center dt-body-center">Nov</th>
                            <th class="dt-head-center dt-body-center">Dic</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-if="filteredGroupPayments.length">

                            <tr v-for="payPlayer in filteredGroupPayments" :key="payPlayer.id">
                                <td class="dt-head-center dt-body-center">
                                    <div class="media d-md-flex d-block text-sm-start text-center">
                                        <div class="media-aside align-self-start avatar avatar-sm me-1">
                                            <img :src="payPlayer.player.photo_url" alt="avatar"
                                                class="player-avatar" />
                                        </div>
                                        <div class="media-body">
                                            <div class="d-xl-flex d-block justify-content-between">
                                                <div>
                                                    <small>
                                                        {{ payPlayer.player.full_names }}
                                                        <span v-if="payPlayer.inscription_deleted" class="badge bg-warning text-dark ms-2">
                                                            Inscripción retirada
                                                        </span>
                                                    </small>
                                                    <p>
                                                        <small>
                                                            {{ payPlayer.player.unique_code }}
                                                            <span>
                                                                | {{ payPlayer.player.category }}
                                                            </span>
                                                        </small>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="dt-head-center dt-body-center"
                                    v-for="field in paymentFields"
                                    :key="field"
                                    :data-payment-id="payPlayer.id"
                                    :data-payment-field="field"
                                >

                                    <template
                                        v-if="editingCell?.payPlayer === payPlayer && editingCell?.field === field && canEditPaymentRow(payPlayer, field)">
                                        <div class="d-flex flex-column gap-1">
                                            <select v-model="payPlayer[field]" :id="`select_${field}_${payPlayer.id}`"
                                                :name="`select_${field}_${payPlayer.id}`" autocomplete="off"
                                                @change="handleSelectChange(payPlayer, field)"
                                                class="form-select form-select-sm mb-1">
                                                <option v-for="type in type_payments" :key="type.value"
                                                    :value="type.value">{{ type.label }}</option>
                                            </select>
                                            <CurrencyInput class="form-control form-control-sm mb-1"
                                                v-model="payPlayer[`${field}_amount`]"
                                                :id="`input_${field}_${payPlayer.id}`"
                                                :name="`input_${field}_${payPlayer.id}`" autocomplete="off"
                                                placeholder="Monto" />

                                            <div class="d-flex justify-content-center gap-1">
                                                <span class="badge badge-success clickable" @click="saveField"
                                                    title="Guardar">
                                                    <i class="far fa-check-square fa-lg"></i>
                                                </span>
                                                <span class="badge badge-info clickable" @click="cancelEdition(field)"
                                                    title="Cancelar">
                                                    <i class="far fa-window-close fa-lg"></i>
                                                </span>

                                            </div>
                                        </div>
                                    </template>

                                    <template v-else>
                                        <span :class="`badge payments-c-${payPlayer[field]}`">
                                            {{ paymentTypeLabels[String(payPlayer[field])] }}
                                        </span>
                                        <br />
                                        <small class="text-muted">
                                            {{ moneyFormat(payPlayer[`${field}_amount`]) }}
                                        </small>
                                        <span v-if="canEditPaymentRow(payPlayer, field)"
                                            class="badge badge-light btn btn-sm clickable"
                                            @click="editRow(payPlayer, field)">
                                            <i class="far fa-edit"></i>
                                        </span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td colspan="14" class="dt-head-center dt-body-center">
                                    <span class="text-muted">No se encontraron resultados</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    <tfoot v-if="filteredGroupPayments.length">
                        <tr>
                            <th class="dt-head-center dt-body-center">
                                <span class="text-muted">Pagos Totales</span>
                            </th>
                            <th class="dt-head-center dt-body-center" v-for="field in totalsFooter">
                                <span class="text-muted">{{ moneyFormat(field) }}</span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </template>
    </panel>
    <breadcrumb :parent="'Plataforma'" :current="'Mensualidades'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script>
export default {
    name: 'monthly-payment-list'
}
</script>
<script setup>
import { nextTick, ref, watch } from 'vue'
import CurrencyInput from '@/components/general/CurrencyInput';
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useMonthlyPayments from '@/composables/payments/monthly-payments';
import { usePageTutorial } from '@/composables/usePageTutorial'
import { ErrorMessage, Field, Form } from 'vee-validate';
import { monthlyPaymentsTutorial } from '@/tutorials/payments'

const {
    handleSearch,
    editRow,
    cancelEdition,
    handleSelectChange,
    saveField,
    exportFile,
    isLoading,
    player_count,
    selected_group,
    export_excel,
    export_pdf,
    modelGroup,
    modelCategory,
    groupPayments,
    playerSearchTerm,
    filteredGroupPayments,
    visiblePlayerCount,
    schema,
    formData,
    editingCell,
    groups,
    years,
    categories,
    type_payments,
    paymentTypeLabels,
    monthOptions,
    statusOptions,
    canEditPaymentRow,
    paymentFields,
    viewMode,
    bulkStatus,
    bulkAmount,
    bulkStatusOptions,
    bulkEligibleRows,
    isBulkUpdating,
    applyBulkPaymentStatus,
    monthlyRows,
    selectedMonthLabel,
    debtorCount,
    receiptableCount,
    retiredRowsCount,
    totalsFooter,
    totalByType
} = useMonthlyPayments()
const tutorial = usePageTutorial(monthlyPaymentsTutorial)

const scrollContainer = ref(null)

const waitForAnimationFrame = () => new Promise((resolve) => requestAnimationFrame(resolve))

const findEditingCellElement = (cell = editingCell.value) => {
    if (!cell?.payPlayer?.id || !cell?.field || !scrollContainer.value) {
        return null
    }

    return scrollContainer.value.querySelector(
        `[data-payment-id="${cell.payPlayer.id}"][data-payment-field="${cell.field}"]`
    )
}

const keepEditingCellVisible = (cellElement) => {
    if (!scrollContainer.value || !cellElement) {
        return
    }

    const containerRect = scrollContainer.value.getBoundingClientRect()
    const cellRect = cellElement.getBoundingClientRect()
    const tableHeadHeight = scrollContainer.value.querySelector('thead')?.getBoundingClientRect().height || 0
    const tableFootHeight = scrollContainer.value.querySelector('tfoot')?.getBoundingClientRect().height || 0
    const verticalPadding = 12
    const horizontalPadding = 16

    const visibleTop = containerRect.top + tableHeadHeight + verticalPadding
    const visibleBottom = containerRect.bottom - tableFootHeight - verticalPadding
    const visibleLeft = containerRect.left + horizontalPadding
    const visibleRight = containerRect.right - horizontalPadding

    if (cellRect.top < visibleTop) {
        scrollContainer.value.scrollTop -= visibleTop - cellRect.top
    } else if (cellRect.bottom > visibleBottom) {
        scrollContainer.value.scrollTop += cellRect.bottom - visibleBottom
    }

    if (cellRect.left < visibleLeft) {
        scrollContainer.value.scrollLeft -= visibleLeft - cellRect.left
    } else if (cellRect.right > visibleRight) {
        scrollContainer.value.scrollLeft += cellRect.right - visibleRight
    }
}

const focusEditingControl = (cellElement) => {
    const control = cellElement?.querySelector('select, input')
    if (!control) {
        return
    }

    try {
        control.focus({ preventScroll: true })
    } catch {
        control.focus()
    }
}

watch(editingCell, async (cell) => {
    if (!cell) {
        return
    }

    await nextTick()
    await waitForAnimationFrame()

    const cellElement = findEditingCellElement(cell)
    if (!cellElement) {
        return
    }

    keepEditingCellVisible(cellElement)
    focusEditingControl(cellElement)
    await nextTick()
    keepEditingCellVisible(cellElement)
})
</script>
