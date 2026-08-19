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
                                <div class="col-lg-5" data-tour="received-payment-report-context">
                                    <h4 class="mb-2">Informe de pagos</h4>
                                    <p class="text-muted mb-3">
                                        Exporta los valores actualmente pagados o abonados por cada deportista.
                                    </p>

                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <h6 class="mb-3">¿Cómo se entrega el informe?</h6>

                                            <div class="mb-3">
                                                <strong>Un grupo: descarga inmediata</strong>
                                                <p class="text-muted mb-0">
                                                    Como contiene menos información, el PDF se genera y abre en una nueva pestaña.
                                                </p>
                                            </div>

                                            <div>
                                                <strong>Todos los grupos: envío por correo</strong>
                                                <p class="text-muted mb-0">
                                                    Como puede incluir muchos pagos, se procesa en segundo plano para evitar lentitud.
                                                    Cuando termine, recibirás el PDF en el correo de tu usuario.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info mt-3 mb-0">
                                        El año seleccionado corresponde al año de la obligación pagada.
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm mt-3" @click="tutorial.start()">
                                        <i class="fa-regular fa-circle-question me-2"></i>Guía
                                    </button>
                                </div>

                                <div class="col-lg-7" data-tour="received-payment-report-filters">
                                    <div v-if="loadError" class="alert alert-danger">
                                        {{ loadError }}
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label" for="received-payment-report-year">Año</label>
                                            <CustomSelect2
                                                id="received-payment-report-year"
                                                v-model="form.year"
                                                :options="years"
                                                :disabled="isLoading"
                                                placeholder="Selecciona un año" />
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label" for="received-payment-report-group">Grupo de entrenamiento</label>
                                            <CustomSelect2
                                                id="received-payment-report-group"
                                                v-model="form.training_group_id"
                                                :options="groups"
                                                :disabled="isLoading"
                                                placeholder="Todos los grupos" />
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="received-payment-report-player-search">
                                                Deportista
                                            </label>
                                            <input
                                                id="received-payment-report-player-search"
                                                v-model.trim="form.player_search"
                                                type="search"
                                                class="form-control"
                                                maxlength="120"
                                                placeholder="Buscar por nombre o código único">
                                            <div class="form-text">
                                                Puedes escribir parte del nombre, apellido o código del deportista.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-body py-3">
                                            <h6 class="mb-3">Opciones del PDF</h6>
                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <div class="form-check mb-0">
                                                        <input
                                                            id="received-payment-report-item-amounts"
                                                            v-model="form.show_item_amounts"
                                                            class="form-check-input"
                                                            type="checkbox">
                                                        <label class="form-check-label" for="received-payment-report-item-amounts">
                                                            Mostrar valores por concepto
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-check mb-0">
                                                        <input
                                                            id="received-payment-report-total"
                                                            v-model="form.show_total_paid"
                                                            class="form-check-input"
                                                            type="checkbox">
                                                        <label class="form-check-label" for="received-payment-report-total">
                                                            Mostrar total pagado
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid d-md-flex justify-content-md-end" data-tour="received-payment-report-actions">
                                        <button
                                            type="button"
                                            class="btn btn-primary px-4"
                                            :disabled="isLoading || isSubmitting || !exportUrl"
                                            @click="exportReport">
                                            {{ form.training_group_id ? 'Exportar PDF' : 'Enviar por correo' }}
                                            <span v-if="isSubmitting" class="spinner-border spinner-border-sm ms-2" aria-hidden="true"></span>
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
    <breadcrumb :parent="'Informes'" :current="'Pagos'" />
</template>

<script>
export default {
    name: 'received-payment-reports-index',
}
</script>

<script setup>
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useReceivedPaymentReport from '@/composables/reports/received-payment-report'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { receivedPaymentReportTutorial } from '@/tutorials/reports'

const tutorial = usePageTutorial(receivedPaymentReportTutorial)
const {
    exportReport,
    exportUrl,
    form,
    groups,
    isLoading,
    isSubmitting,
    loadError,
    years,
} = useReceivedPaymentReport()
</script>
