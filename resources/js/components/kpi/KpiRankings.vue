<template>
    <section>
        <div class="kpi-content-heading mb-3">
            <p class="kpi-eyebrow mb-1">Priorización</p>
            <h4 class="mb-1">Rankings por grupo</h4>
            <p class="text-muted mb-0">Identifica rápidamente los mejores resultados y los frentes que requieren seguimiento.</p>
        </div>

        <div class="row g-3 pb-4" data-tour="kpi-rankings">
            <div v-for="section in sections" :key="section.key" class="col-12 col-xl-3 col-md-6">
                <div :class="['panel br-6 h-100 kpi-ranking-card', section.tone]">
                    <div class="panel-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="kpi-ranking-card__icon" aria-hidden="true">
                                <i :class="section.icon"></i>
                            </span>
                            <div>
                                <h5 class="mb-1">{{ section.title }}</h5>
                                <p class="text-muted small mb-0">{{ section.description }}</p>
                            </div>
                        </div>

                        <div v-if="section.items.length === 0" class="kpi-ranking-empty text-muted small">
                            <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                            <span>No hay grupos suficientes para rankear este indicador.</span>
                        </div>

                        <div v-else class="kpi-ranking-list">
                            <div
                                v-for="(item, index) in section.items"
                                :key="`${section.key}-${item.training_group_id}`"
                                class="kpi-ranking-item d-flex justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <span class="kpi-ranking-item__position">{{ index + 1 }}</span>
                                    <span class="small text-truncate">{{ item.label }}</span>
                                </div>
                                <strong class="kpi-ranking-item__value text-nowrap">
                                    {{ formatMetricValue(item.value, item.format) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'

import { formatMetricValue } from './kpiFormatters'

const props = defineProps({
    rankings: { type: Object, default: () => ({}) },
})

const sections = computed(() => ([
    {
        key: 'compliance',
        title: 'Mejor cumplimiento',
        description: 'Grupos con mejor porcentaje acumulado de cumplimiento.',
        items: props.rankings?.compliance ?? [],
        icon: 'fa-solid fa-trophy',
        tone: 'kpi-tone-success',
    },
    {
        key: 'low_attendance',
        title: 'Menor asistencia',
        description: 'Grupos que requieren atención por su porcentaje del mes.',
        items: props.rankings?.low_attendance ?? [],
        icon: 'fa-solid fa-person-circle-exclamation',
        tone: 'kpi-tone-warning',
    },
    {
        key: 'debt',
        title: 'Mayor deuda',
        description: 'Grupos con más mensualidades en estado de deuda.',
        items: props.rankings?.debt ?? [],
        icon: 'fa-solid fa-receipt',
        tone: 'kpi-tone-danger',
    },
    {
        key: 'flagged',
        title: 'Más observados',
        description: 'Grupos con más deportistas observados en el cruce pago vs asistencia.',
        items: props.rankings?.flagged ?? [],
        icon: 'fa-solid fa-eye',
        tone: 'kpi-tone-indigo',
    },
]))
</script>
