<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <panel>
                    <template #body>
                        <div v-if="isLoading && !isReady" class="py-5 text-center text-muted">
                            Cargando configuración del reporte...
                        </div>

                        <template v-else-if="isReady">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                                <div>
                                    <h3 class="mb-1">Mensualidades vs asistencias</h3>
                                    <p class="text-muted mb-0">
                                        Detecta deportistas que asistieron a entrenamientos sin tener la mensualidad del mes completamente al día.
                                    </p>
                                </div>
                            </div>

                            <section class="border rounded-3 shadow-sm p-3 p-lg-4 mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label" for="attendance-payment-year">Año</label>
                                        <CustomSelect2
                                            id="attendance-payment-year"
                                            v-model="filters.year"
                                            :options="years"
                                            placeholder="Selecciona un año" />
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label" for="attendance-payment-month">Mes</label>
                                        <CustomSelect2
                                            id="attendance-payment-month"
                                            v-model="filters.month"
                                            :options="months"
                                            placeholder="Selecciona un mes" />
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label" for="attendance-payment-group">Grupo</label>
                                        <CustomSelect2
                                            id="attendance-payment-group"
                                            v-model="filters.training_group_id"
                                            :options="groups"
                                            placeholder="Todos los grupos" />
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="button" class="btn btn-primary w-100" @click="searchReports">
                                            Consultar
                                        </button>
                                    </div>
                                </div>
                            </section>

                            <section class="border rounded-3 shadow-sm p-3 p-lg-4 mb-4">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h4 class="mb-1">Resumen por grupo</h4>
                                        <p class="text-muted mb-0">
                                            Revisa cuántos deportistas asistieron y cuántos quedaron observados en el período consultado.
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a :href="summaryExcelUrl" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-success btn-sm">
                                            Excel
                                        </a>
                                        <a :href="summaryPdfUrl" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-danger btn-sm">
                                            PDF
                                        </a>
                                    </div>
                                </div>

                                <div class="table-responsive-sm">
                                    <DatatableTemplate
                                        id="attendance-payment-summary-table"
                                        ref="summaryTable"
                                        :options="summaryOptions">
                                        <template #thead>
                                            <thead>
                                                <tr>
                                                    <th v-for="column in summaryColumns" :key="column.data">
                                                        {{ column.title }}
                                                    </th>
                                                </tr>
                                            </thead>
                                        </template>
                                    </DatatableTemplate>
                                </div>
                            </section>

                            <section class="border rounded-3 shadow-sm p-3 p-lg-4">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h4 class="mb-1">Detalle por deportista</h4>
                                        <p class="text-muted mb-0">
                                            Este listado muestra únicamente los deportistas observados porque sí registraron asistencia y la mensualidad del mes no quedó pagada totalmente.
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a :href="playerExcelUrl" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-success btn-sm">
                                            Excel
                                        </a>
                                        <a :href="playerPdfUrl" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-danger btn-sm">
                                            PDF
                                        </a>
                                    </div>
                                </div>

                                <div class="table-responsive-sm">
                                    <DatatableTemplate
                                        id="attendance-payment-player-table"
                                        ref="playerTable"
                                        :options="playerOptions">
                                        <template #thead>
                                            <thead>
                                                <tr>
                                                    <th v-for="column in playerColumns" :key="column.data">
                                                        {{ column.title }}
                                                    </th>
                                                </tr>
                                            </thead>
                                        </template>
                                    </DatatableTemplate>
                                </div>
                            </section>
                        </template>

                        <div v-else class="alert alert-danger mb-0">
                            {{ loadError || 'No fue posible cargar el reporte.' }}
                        </div>
                    </template>
                </panel>
            </div>
        </div>
    </div>

    <breadcrumb :parent="'Informes'" :current="'Mensualidades vs asistencias'" />
</template>

<script>
export default {
    name: 'attendance-payment-reports-index',
}
</script>

<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import useAttendancePaymentReport from '@/composables/reports/attendance-payment-report'

const {
    filters,
    groups,
    isLoading,
    isReady,
    loadError,
    months,
    playerColumns,
    playerExcelUrl,
    playerOptions,
    playerPdfUrl,
    playerTable,
    searchReports,
    summaryColumns,
    summaryExcelUrl,
    summaryOptions,
    summaryPdfUrl,
    summaryTable,
    years,
} = useAttendancePaymentReport()
</script>
