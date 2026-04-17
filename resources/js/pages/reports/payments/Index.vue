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
                                <h4 class="mb-2">Informe de pagos</h4>
                                <p class="text-muted mb-3">
                                    Solicita el consolidado por año o por año y grupo y recíbelo por correo cuando esté listo.
                                </p>

                                <div class="alert alert-info mb-0">
                                    Puedes dejar el grupo vacío para generar el informe completo del año seleccionado.
                                    Como este reporte puede incluir demasiados registros, se procesa en segundo plano y se envía a tu correo.
                                </div>
                            </div>

                            <div class="col-lg-7">
                                <div v-if="loadError" class="alert alert-danger">
                                    {{ loadError }}
                                </div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label" for="payment-report-year">Año</label>
                                        <CustomSelect2
                                            id="payment-report-year"
                                            v-model="form.year"
                                            :options="years"
                                            :disabled="isLoading || isSubmitting"
                                            placeholder="Selecciona un año" />
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label" for="payment-report-group">Grupo de entrenamiento</label>
                                        <CustomSelect2
                                            id="payment-report-group"
                                            v-model="form.training_group_id"
                                            :options="groups"
                                            :disabled="isLoading || isSubmitting"
                                            placeholder="Todos los grupos" />
                                    </div>

                                    <div class="col-md-3">
                                        <button
                                            type="button"
                                            class="btn btn-primary w-100"
                                            :disabled="isLoading || isSubmitting"
                                            @click="sendByEmail">
                                            Solicitar por correo
                                            <template v-if="isSubmitting">
                                                &nbsp;
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-loader spin">
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
                                </div>
                            </div>
                        </div>
                        </template>
                    </template>
                </panel>
            </div>
        </div>
    </div>

    <breadcrumb :parent="'Informes'" :current="'Mensualidades'" />
</template>

<script>
export default {
    name: 'payment-reports-index',
}
</script>

<script setup>
import usePaymentReport from '@/composables/reports/payment-report'

const {
    form,
    groups,
    isLoading,
    isSubmitting,
    loadError,
    sendByEmail,
    years,
} = usePaymentReport()
</script>
