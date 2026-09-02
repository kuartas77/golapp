<template>
    <div class="panel br-6 kpi-hero overflow-hidden" data-tour="kpi-toolbar">
        <div class="panel-body kpi-hero__body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
            <div class="d-flex align-items-start gap-3">
                <span class="kpi-hero__icon" aria-hidden="true">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <div>
                    <p class="kpi-eyebrow mb-2">Panel ejecutivo</p>
                    <h3 class="kpi-hero__title mb-2">Indicadores del backoffice</h3>
                    <p class="kpi-hero__description text-muted mb-0">
                        Consolida recaudo, cumplimiento, asistencia y alertas operativas sin salir de la portada.
                    </p>
                </div>
            </div>

            <div class="kpi-hero__actions d-flex gap-2 flex-wrap align-items-center">
                <span v-if="isLoading && isReady" class="kpi-updating-indicator small" role="status">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    Actualizando indicadores...
                </span>
                <button type="button" class="btn btn-info btn-sm kpi-guide-button" @click="$emit('start-tutorial')">
                    <i class="fa-regular fa-circle-question me-2"></i>
                    Guía
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    isLoading: { type: Boolean, required: true },
    isReady: { type: Boolean, required: true },
})

defineEmits(['start-tutorial'])
</script>
