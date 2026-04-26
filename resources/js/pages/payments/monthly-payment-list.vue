<template>
    <panel>
        <template #body>

            <div class="row" data-tour="monthly-payments-filters">

                <div class="col-xl-6 col-lg-6 col-sm-12 text-center">
                    <Form ref="form" :validation-schema="schema" @submit="handleSearch" :initial-values="formData"
                        class="row align-items-center justify-content-center">
                        <p class="text-muted">Puedes seleccionar un grupo y/o una categoría, en otro caso combinarlos.
                        </p>
                        <div class="col-sm-4">
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
                        <div class="col-sm-3">
                            <label for="year" class="sr-only">Año</label>
                            <Field name="year" as="CustomSelect2" :options="years" id="year"
                                placeholder="Año" />
                            <ErrorMessage name="year" class="custom-error" />
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
                            <span class="badge outline-badge-danger  me-1">
                                Deben {{ moneyFormat(totalByType.debts) }}
                            </span>
                            <!-- <span class="badge outline-badge-info  me-1">
                                Otros {{ moneyFormat(totalByType.others) }}
                            </span> -->



                        </div>

                        <div class="col-12 mt-1">
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
                            Guia
                        </button>
                    </div>
                </div>

                <div
                    class="d-md-flex justify-content-between align-items-center dt-layout-end col-md-auto ms-auto mb-2">
                    <div class="dt-info">
                        Mostrando {{ player_count }} Deportistas.
                    </div>
                </div>
            </div>

            <div
                ref="scrollContainer"
                class="table-responsive"
                :class="groupPayments.length ? 'scroll-container' : ''"
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
                        <template v-if="groupPayments.length">

                            <tr v-for="(payPlayer, index) in groupPayments" :key="index">
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
                                        v-if="editingCell?.payPlayer === payPlayer && editingCell?.field === field && !typesNoEditables.some((e) => e === payPlayer[field])">
                                        <div class="d-flex flex-column gap-1">
                                            <select v-model="payPlayer[field]" :id="`select_${field}_${payPlayer.id}`"
                                                :name="`select_${field}_${payPlayer.id}`" autocomplete="off"
                                                @change="handleSelectChange(payPlayer, field)"
                                                class="form-select form-select-sm">
                                                <option v-for="type in type_payments" :key="type.value"
                                                    :value="type.value">{{ type.label }}</option>
                                            </select>
                                            <CurrencyInput class="form-control form-control-sm"
                                                v-model="payPlayer[`${field}_amount`]"
                                                :id="`input_${field}_${payPlayer.id}`"
                                                :name="`input_${field}_${payPlayer.id}`" autocomplete="off"
                                                placeholder="Monto" />

                                            <div class="d-flex justify-content-between">
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
                                        <span v-if="!typesNoEditables.some((e) => e === payPlayer[field])"
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

                    <tfoot v-if="groupPayments.length">
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
    schema,
    formData,
    editingCell,
    groups,
    years,
    categories,
    type_payments,
    paymentTypeLabels,
    typesNoEditables,
    paymentFields,
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
