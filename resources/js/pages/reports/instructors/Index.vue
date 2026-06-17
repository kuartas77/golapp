<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <panel>
                    <template #body>
                        <div v-if="isLoading && !isReady" class="py-5 text-center text-muted">
                            Cargando informe...
                        </div>

                        <template v-else-if="isReady">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                                <div>
                                    <h3 class="mb-1">Actividad de instructores</h3>
                                    <p class="text-muted mb-0">
                                        Revisa el resumen mensual de asistencias, competencias, metodologías y sesiones.
                                    </p>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a
                                        :href="excelUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-success btn-sm">
                                        Excel
                                    </a>
                                    <a
                                        :href="pdfUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-danger btn-sm">
                                        PDF
                                    </a>
                                </div>
                            </div>

                            <div v-if="loadError" class="alert alert-danger">
                                {{ loadError }}
                            </div>

                            <div class="row g-3 align-items-end mb-4">
                                <div class="col-12 col-md-3">
                                    <label class="form-label" for="instructor-activity-year">Año</label>
                                    <CustomSelect2
                                        id="instructor-activity-year"
                                        v-model="filters.year"
                                        :options="years"
                                        placeholder="Selecciona un año" />
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label" for="instructor-activity-month">Mes</label>
                                    <CustomSelect2
                                        id="instructor-activity-month"
                                        v-model="filters.month"
                                        :options="months"
                                        placeholder="Selecciona un mes" />
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="instructor-activity-instructor">Instructor</label>
                                    <CustomSelect2
                                        id="instructor-activity-instructor"
                                        v-model="filters.instructor_id"
                                        :options="instructors"
                                        placeholder="Todos los instructores" />
                                </div>

                                <div class="col-12 col-md-2">
                                    <button type="button" class="btn btn-primary w-100" @click="search">
                                        Consultar
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive-sm">
                                <DatatableTemplate
                                    id="instructor-activity-report-table"
                                    ref="instructorActivityTable"
                                    :options="options">
                                    <template #thead>
                                        <thead>
                                            <tr>
                                                <th v-for="column in columns" :key="column.data">
                                                    {{ column.title }}
                                                </th>
                                            </tr>
                                        </thead>
                                    </template>
                                </DatatableTemplate>
                            </div>
                        </template>

                        <div v-else class="alert alert-danger mb-0">
                            {{ loadError || 'No fue posible cargar el informe.' }}
                        </div>
                    </template>
                </panel>
            </div>
        </div>
    </div>

    <breadcrumb :parent="'Informes'" :current="'Actividad de instructores'" />
</template>

<script>
export default {
    name: 'instructor-activity-reports-index',
}
</script>

<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import useInstructorActivityReport from '@/composables/reports/instructor-activity-report'

const {
    columns,
    excelUrl,
    filters,
    instructors,
    isLoading,
    isReady,
    loadError,
    months,
    options,
    pdfUrl,
    search,
    years,
} = useInstructorActivityReport()
</script>
