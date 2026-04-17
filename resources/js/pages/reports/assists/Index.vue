<template>
    <div class="layout-px-spacing">
        <div v-if="isLoading && !isReady" class="row layout-top-spacing">
            <div class="col-12">
                <panel>
                    <template #body>
                        <div class="py-5 text-center text-muted">
                            Cargando configuración de reportes...
                        </div>
                    </template>
                </panel>
            </div>
        </div>

        <div v-else-if="isReady" class="row layout-top-spacing">
            <div class="col-12">
                <panel>
                    <template #body>
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">Reporte mensual por jugador</h4>
                                <p class="text-muted mb-0">
                                    Consulta el consolidado mensual por deportista y exporta el resultado en Excel o PDF.
                                </p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a :href="monthlyPlayerExcelUrl" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-success btn-sm">
                                    Excel
                                </a>
                                <a :href="monthlyPlayerPdfUrl" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-danger btn-sm">
                                    PDF
                                </a>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label class="form-label" for="monthly-player-year">Año</label>
                                <CustomSelect2
                                    id="monthly-player-year"
                                    v-model="monthlyPlayerFilters.year"
                                    :options="years"
                                    placeholder="Selecciona un año" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="monthly-player-month">Mes</label>
                                <CustomSelect2
                                    id="monthly-player-month"
                                    v-model="monthlyPlayerFilters.month"
                                    :options="months"
                                    placeholder="Selecciona un mes" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="monthly-player-group">Grupo</label>
                                <CustomSelect2
                                    id="monthly-player-group"
                                    v-model="monthlyPlayerFilters.training_group_id"
                                    :options="groupOptions"
                                    placeholder="Todos los grupos" />
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" @click="searchMonthlyPlayer">
                                    Consultar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <DatatableTemplate
                                id="monthly-player-report-table"
                                ref="monthlyPlayerTable"
                                :options="monthlyPlayerOptions">
                                <template #thead>
                                    <thead>
                                        <tr>
                                            <th v-for="column in monthlyPlayerColumns" :key="column.data">
                                                {{ column.title }}
                                            </th>
                                        </tr>
                                    </thead>
                                </template>
                            </DatatableTemplate>
                        </div>
                    </template>
                </panel>
            </div>

            <div class="col-12">
                <panel>
                    <template #body>
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">Reporte mensual por grupo</h4>
                                <p class="text-muted mb-0">
                                    Revisa el comportamiento mensual agregado por grupo de entrenamiento.
                                </p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a :href="monthlyGroupExcelUrl" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-success btn-sm">
                                    Excel
                                </a>
                                <a :href="monthlyGroupPdfUrl" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-danger btn-sm">
                                    PDF
                                </a>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label class="form-label" for="monthly-group-year">Año</label>
                                <CustomSelect2
                                    id="monthly-group-year"
                                    v-model="monthlyGroupFilters.year"
                                    :options="years"
                                    placeholder="Selecciona un año" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="monthly-group-month">Mes</label>
                                <CustomSelect2
                                    id="monthly-group-month"
                                    v-model="monthlyGroupFilters.month"
                                    :options="months"
                                    placeholder="Selecciona un mes" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="monthly-group-group">Grupo</label>
                                <CustomSelect2
                                    id="monthly-group-group"
                                    v-model="monthlyGroupFilters.training_group_id"
                                    :options="groupOptions"
                                    placeholder="Todos los grupos" />
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" @click="searchMonthlyGroup">
                                    Consultar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <DatatableTemplate
                                id="monthly-group-report-table"
                                ref="monthlyGroupTable"
                                :options="monthlyGroupOptions">
                                <template #thead>
                                    <thead>
                                        <tr>
                                            <th v-for="column in monthlyGroupColumns" :key="column.data">
                                                {{ column.title }}
                                            </th>
                                        </tr>
                                    </thead>
                                </template>
                            </DatatableTemplate>
                        </div>
                    </template>
                </panel>
            </div>

            <div class="col-12">
                <panel>
                    <template #body>
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">Reporte anual consolidado</h4>
                                <p class="text-muted mb-0">
                                    Consolida la asistencia del año seleccionado y filtra por grupo cuando lo necesites.
                                </p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a :href="annualConsolidatedExcelUrl" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-success btn-sm">
                                    Excel
                                </a>
                                <a :href="annualConsolidatedPdfUrl" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-danger btn-sm">
                                    PDF
                                </a>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label class="form-label" for="annual-consolidated-year">Año</label>
                                <CustomSelect2
                                    id="annual-consolidated-year"
                                    v-model="annualConsolidatedFilters.year"
                                    :options="years"
                                    placeholder="Selecciona un año" />
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="annual-consolidated-group">Grupo</label>
                                <CustomSelect2
                                    id="annual-consolidated-group"
                                    v-model="annualConsolidatedFilters.training_group_id"
                                    :options="groupOptions"
                                    placeholder="Todos los grupos" />
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" @click="searchAnnualConsolidated">
                                    Consultar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <DatatableTemplate
                                id="annual-consolidated-report-table"
                                ref="annualConsolidatedTable"
                                :options="annualConsolidatedOptions">
                                <template #thead>
                                    <thead>
                                        <tr>
                                            <th v-for="column in annualConsolidatedColumns" :key="column.data">
                                                {{ column.title }}
                                            </th>
                                        </tr>
                                    </thead>
                                </template>
                            </DatatableTemplate>
                        </div>
                    </template>
                </panel>
            </div>
        </div>

        <div v-else class="row layout-top-spacing">
            <div class="col-12">
                <div class="alert alert-danger mb-0">
                    {{ loadError || 'No fue posible cargar los reportes.' }}
                </div>
            </div>
        </div>
    </div>

    <breadcrumb :parent="'Informes'" :current="'Asistencias'" />
</template>

<script>
export default {
    name: 'assist-reports-index',
}
</script>

<script setup>
import useAssistReports from '@/composables/reports/assist-reports'

const {
    annualConsolidatedColumns,
    annualConsolidatedExcelUrl,
    annualConsolidatedFilters,
    annualConsolidatedOptions,
    annualConsolidatedPdfUrl,
    annualConsolidatedTable,
    groupOptions,
    isLoading,
    isReady,
    loadError,
    monthlyGroupColumns,
    monthlyGroupExcelUrl,
    monthlyGroupFilters,
    monthlyGroupOptions,
    monthlyGroupPdfUrl,
    monthlyGroupTable,
    monthlyPlayerColumns,
    monthlyPlayerExcelUrl,
    monthlyPlayerFilters,
    monthlyPlayerOptions,
    monthlyPlayerPdfUrl,
    monthlyPlayerTable,
    months,
    searchAnnualConsolidated,
    searchMonthlyGroup,
    searchMonthlyPlayer,
    years,
} = useAssistReports()
</script>
