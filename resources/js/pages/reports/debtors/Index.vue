<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <panel>
                    <template #body>
                        <div v-if="isLoading && !years.length" class="py-5 text-center text-muted">
                            Cargando opciones del informe...
                        </div>

                        <template v-else>
                            <div class="row g-4 align-items-start">
                                <div class="col-lg-5">
                                    <h4 class="mb-2">Informe de deudores</h4>
                                    <p class="text-muted mb-3">
                                        Exporta el consolidado por deportista con deuda de mensualidades y facturas.
                                    </p>

                                    <div class="alert alert-info mb-0">
                                        Puedes dejar el grupo vacío para generar el informe completo del año seleccionado.
                                    </div>
                                </div>

                                <div class="col-lg-7">
                                    <div v-if="loadError" class="alert alert-danger">
                                        {{ loadError }}
                                    </div>

                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label" for="debtor-report-year">Año</label>
                                            <CustomSelect2
                                                id="debtor-report-year"
                                                v-model="form.year"
                                                :options="years"
                                                :disabled="isLoading"
                                                placeholder="Selecciona un año" />
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="debtor-report-group">Grupo de entrenamiento</label>
                                            <CustomSelect2
                                                id="debtor-report-group"
                                                v-model="form.training_group_id"
                                                :options="groups"
                                                :disabled="isLoading"
                                                placeholder="Todos los grupos" />
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-check mb-md-2">
                                                <input
                                                    id="debtor-report-total"
                                                    v-model="form.show_total_debt"
                                                    class="form-check-input"
                                                    type="checkbox">
                                                <label class="form-check-label" for="debtor-report-total">
                                                    Mostrar total
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <button
                                                type="button"
                                                class="btn btn-primary w-100"
                                                :disabled="isLoading || !exportUrl"
                                                @click="exportPdf">
                                                Exportar PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                </panel>
            </div>
        </div>
    </div>

    <breadcrumb :parent="'Informes'" :current="'Deudores'" />
</template>

<script>
export default {
    name: 'debtor-reports-index',
}
</script>

<script setup>
import useDebtorReport from '@/composables/reports/debtor-report'

const {
    exportPdf,
    exportUrl,
    form,
    groups,
    isLoading,
    loadError,
    years,
} = useDebtorReport()
</script>
