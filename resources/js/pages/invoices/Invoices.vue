<template>
    <panel>
        <template #body>


            <div class="row " data-tour="invoices-index-filters">
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
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <flat-pickr :config="flatpickrConfig" class="form-control form-control-sm flatpickr"
                        id="filterDate" v-model="filterDate" placeholder="Rango fecha facturación"></flat-pickr>
                            <span class="input-group-text" @click="clearDate">
                                <i class="fa-solid fa-x" ></i>
                            </span>

                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>
            </div>

            <div v-if="groupOptionsLoaded" data-tour="invoices-index-table">
                <DatatableTemplate :options="options" :id="'invoives_table'" ref="invoives_table" @click="onClickRow">
                <template #thead>
                    <thead>
                        <tr>
                            <th>
                                <div>
                                    <input
                                        v-model="invoiceNumberFilter"
                                        type="search"
                                        class="form-control form-control-sm datatable-header-filter-input"
                                        placeholder="# Factura"
                                        aria-label="Buscar por número de factura"
                                        autocomplete="off"
                                        @click.stop
                                        @keydown.stop
                                        @input="applyInvoiceNumberFilter"
                                    >
                                </div>
                            </th>
                            <th>
                                <div>
                                    <input
                                        v-model="studentNameFilter"
                                        type="search"
                                        class="form-control form-control-sm datatable-header-filter-input"
                                        placeholder="Deportista"
                                        aria-label="Buscar por deportista"
                                        autocomplete="off"
                                        @click.stop
                                        @keydown.stop
                                        @input="applyStudentNameFilter"
                                    >
                                </div>
                            </th>
                            <th>
                                <div>
                                    <select
                                        v-model="trainingGroupFilter"
                                        class="form-select form-select-sm form-select-custom"
                                        aria-label="Filtrar por grupo"
                                        @click.stop
                                        @keydown.stop
                                        @change="applyTrainingGroupFilter"
                                    >
                                        <option value="">Grupo</option>
                                        <option v-for="group in groupOptions" :key="group.value" :value="group.value">
                                            {{ group.label }}
                                        </option>
                                    </select>
                                </div>
                            </th>
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
            </div>

        </template>

    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Facturas'" />
    <PageTutorialOverlay :tutorial="tutorial" />

</template>

<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useInvoicesList from '@/composables/invoices/invoicesList'
import { usePageTutorial } from '@/composables/usePageTutorial'
import dayjs from '@/utils/dayjs';
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";
import { invoicesIndexTutorial } from '@/tutorials/invoices'

const flatpickrConfig = {
    wrap: true,
    mode: "range",
    locale: Spanish,
    maxDate: dayjs().format('YYYY-M-D'),
    minDate: dayjs().subtract(5, 'year').format('YYYY-M-D'),
}
const {
    options,
    invoives_table,
    filterDate,
    clearDate,
    onClickRow,
    reloadTable,
    invoiceNumberFilter,
    studentNameFilter,
    trainingGroupFilter,
    groupOptions,
    groupOptionsLoaded,
    applyInvoiceNumberFilter,
    applyStudentNameFilter,
    applyTrainingGroupFilter,
} = useInvoicesList()
const tutorial = usePageTutorial(invoicesIndexTutorial)
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
