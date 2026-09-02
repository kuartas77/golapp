<template>
    <div class="panel br-6 kpi-reports-card overflow-hidden">
        <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
            <div class="d-flex align-items-start gap-3">
                <span class="kpi-reports-card__icon" aria-hidden="true">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </span>
                <div>
                    <h5 class="mb-1">Bajar al detalle</h5>
                    <p class="text-muted mb-0">
                        Abre los reportes relacionados con este mismo corte para investigar variaciones y alertas.
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap kpi-report-actions">
                <router-link v-if="links.assists" :to="links.assists" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-user-check me-2" aria-hidden="true"></i>
                    Asistencias
                </router-link>
                <router-link v-if="links.payments" :to="links.payments" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-receipt me-2" aria-hidden="true"></i>
                    Pagos
                </router-link>
                <router-link
                    v-if="links.attendance_payment"
                    :to="links.attendance_payment"
                    class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-scale-balanced me-2" aria-hidden="true"></i>
                    Mensualidades vs asistencias
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    links: { type: Object, required: true },
})
</script>
