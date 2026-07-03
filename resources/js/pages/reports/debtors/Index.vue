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
                                <div class="col-lg-5" data-tour="debtor-report-context">
                                    <h4 class="mb-2">Informe de deudores</h4>
                                    <p class="text-muted mb-3">
                                        Exporta el consolidado por deportista con deuda de mensualidades y facturas.
                                    </p>

                                    <div class="alert alert-info mb-0">
                                        Puedes dejar el grupo vacío para generar el informe de todos los grupos.
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm mt-3" @click="tutorial.start()"><i class="fa-regular fa-circle-question me-2"></i>Guía</button>
                                </div>

                                <div class="col-lg-7" data-tour="debtor-report-filters">
                                    <div v-if="loadError" class="alert alert-danger">
                                        {{ loadError }}
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label" for="debtor-report-year">Año</label>
                                            <CustomSelect2
                                                id="debtor-report-year"
                                                v-model="form.year"
                                                :options="years"
                                                :disabled="isLoading"
                                                placeholder="Selecciona un año" />
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label" for="debtor-report-group">Grupo de entrenamiento</label>
                                            <CustomSelect2
                                                id="debtor-report-group"
                                                v-model="form.training_group_id"
                                                :options="groups"
                                                :disabled="isLoading"
                                                placeholder="Todos los grupos" />
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-body py-3">
                                            <h6 class="mb-3">Opciones del PDF</h6>

                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <div class="form-check mb-0">
                                                        <input
                                                            id="debtor-report-item-amounts"
                                                            v-model="form.show_item_amounts"
                                                            class="form-check-input"
                                                            type="checkbox">
                                                        <label class="form-check-label" for="debtor-report-item-amounts">
                                                            Mostrar valores por ítem
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-check mb-0">
                                                        <input
                                                            id="debtor-report-total"
                                                            v-model="form.show_total_debt"
                                                            class="form-check-input"
                                                            type="checkbox">
                                                        <label class="form-check-label" for="debtor-report-total">
                                                            Mostrar total general
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid d-md-flex justify-content-md-end" data-tour="debtor-report-actions">
                                        <button
                                            type="button"
                                            class="btn btn-primary px-4"
                                            :disabled="isLoading || !exportUrl"
                                            @click="exportPdf">
                                            Exportar PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                </panel>
            </div>
        </div>
    </div>

    <PageTutorialOverlay :tutorial="tutorial" />
    <breadcrumb :parent="'Informes'" :current="'Deudores'" />
</template>

<script>
export default {
    name: 'debtor-reports-index',
}
</script>

<script setup>
import useDebtorReport from '@/composables/reports/debtor-report'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { debtorReportTutorial } from '@/tutorials/reports'

const tutorial = usePageTutorial(debtorReportTutorial)

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
