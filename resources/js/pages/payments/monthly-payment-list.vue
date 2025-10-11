<template>
    <panel>
        <template #body>
            <div class="row mb-3">
                <div class="col-xl-6 col-lg-6 col-sm-12 text-center">
                    <Form ref="form" :validation-schema="schema" @submit="handleSearch" :initial-values="formData"
                        class="row align-items-center justify-content-center">
                        <div class="col-sm-8">
                            <label for="training_group" class="sr-only">Grupo</label>
                            <Field name="training_group" v-slot="{ field, handleChange, handleBlur }">
                                <multiselect id="training_group" v-bind="field" @change="handleChange"
                                    @blur="handleBlur" v-model="modelGroup" :options="groups" :multiple="false"
                                    :searchable="true" :preselect-first="false" track-by="id" label="full_group"
                                    placeholder="Grupo" :show-labels="false" open-direction="bottom" />
                            </Field>
                            <ErrorMessage name="training_group" class="custom-error" />
                        </div>
                        <div class="col-sm-2">
                            <label for="category" class="sr-only">Categoría</label>
                            <Field name="category" v-slot="{ field, handleChange, handleBlur }">
                                <multiselect id="category" v-bind="field" @change="handleChange" @blur="handleBlur"
                                    v-model="modelCategory" :options="categories" :multiple="false" :searchable="true"
                                    :preselect-first="false" track-by="category" label="category"
                                    placeholder="Categoría" :show-labels="false" open-direction="bottom" />
                            </Field>
                            <ErrorMessage name="month" class="custom-error" />
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary w-100"
                                :disabled="isLoading || (modelGroup === null && modelCategory === null)">
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

                <div class="col-xl-6 col-lg-6 col-sm-12 text-center">
                    <div class="row">

                        <div class="col-12 mt-1">

                            <span class="badge outline-badge-info me-1">
                                Efectivo {{ moneyFormat(totalByType.cash) }}
                            </span>
                            <span class="badge outline-badge-info me-1">
                                Consignación {{ moneyFormat(totalByType.consignment) }}
                            </span>
                            <span class="badge outline-badge-info me-1">
                                Otros {{ moneyFormat(totalByType.others) }}
                            </span>
                            <span class="badge outline-badge-danger  me-1">
                                Deben {{ moneyFormat(totalByType.debts) }}
                            </span>

                            <div class="btn-group mt-1" role="group">
                                <a v-if="export_pdf" :href="export_pdf" target="_blank"
                                    class="badge badge-info btn btn-sm me-1">
                                    <i class="far fa-file-pdf fa-lg"> PDF</i>
                                </a>
                                <a v-if="export_excel" :href="export_excel" target="_blank"
                                    class="badge badge-info btn btn-sm me-1">
                                    <i class="far fa-file-excel fa-lg"> Excel</i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr v-if="selected_group || groupPayments.length" class="bg-primary border-2 border-top border-primary" />

            <div class="d-md-flex justify-content-between align-items-center dt-layout-start col-md-auto me-auto mb-2">
                <div class="dt-info" aria-live="polite" id="table_info" role="status">
                    Mostrando {{ player_count }} Deportistas, los totales no incluyen el estado <small
                        class="badge payments-c-2">Debe</small>.
                </div>
            </div>

            <div class="table-responsive scroll-container">
                <table class="table table-bordered table-sm dataTable align-middle text-center" ref="payments_table">
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
                                                class="rounded-circle" />
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

                                <td class="dt-head-center dt-body-center" v-for="field in paymentFields" :key="field">

                                    <template
                                        v-if="editingCell?.payPlayer === payPlayer && editingCell?.field === field && !typesNoEditables.some((e) => e === payPlayer[field])">
                                        <div class="d-flex flex-column gap-1">
                                            <select v-model="payPlayer[field]" :id="`select_${field}_${payPlayer.id}`"
                                                :name="`select_${field}_${payPlayer.id}`" autocomplete="off"
                                                @change="handleSelectChange(payPlayer, field)"
                                                class="form-select form-select-sm">
                                                <option v-for="(type, index) in type_payments" :value="index">{{ type }}
                                                </option>
                                            </select>
                                            <CurrencyInput class="form-control form-control-sm"
                                                v-model="payPlayer[`${field}_amount`]"
                                                :id="`input_${field}_${payPlayer.id}`"
                                                :name="`input_${field}_${payPlayer.id}`" autocomplete="off"
                                                placeholder="Monto" />

                                            <div class="d-flex justify-content-between">
                                                <span class="badge badge-success clickable" @click="saveField">
                                                    <i class="far fa-check-square fa-lg"></i>
                                                </span>
                                                <span class="badge badge-secondary clickable"
                                                    @click="cancelEdition(field)">
                                                    <i class="far fa-window-close fa-lg"></i>
                                                </span>

                                            </div>
                                        </div>
                                    </template>

                                    <template v-else>
                                        <span :class="`badge payments-c-${payPlayer[field]}`">
                                            {{ type_payments[payPlayer[field]] }}
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
</template>
<script>
export default {
    name: 'monthly-payment-list'
}
</script>
<script setup>
import CurrencyInput from '@/components/general/CurrencyInput';
import useMonthlyPayments from '@/composables/payments/monthly-payments';
import { ErrorMessage, Field, Form } from 'vee-validate';

const {
    moneyFormat,
    handleSearch,
    editRow,
    cancelEdition,
    handleSelectChange,
    saveField,
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
    categories,
    type_payments,
    typesNoEditables,
    paymentFields,
    totalsFooter,
    totalByType
} = useMonthlyPayments()
</script>