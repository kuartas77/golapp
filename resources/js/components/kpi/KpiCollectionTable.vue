<template>
    <div class="panel br-6 kpi-collection-table" data-tour="kpi-collection">
        <div class="kpi-collection-table__header">
            <div>
                <h5 class="mb-1">{{ title }}</h5>
                <p class="text-muted mb-0">{{ description }}</p>
            </div>
            <span class="kpi-chart-count">{{ formatGroupCount(rows.length) }}</span>
        </div>

        <div
            v-if="rows.length"
            class="kpi-collection-table__body"
            role="region"
            aria-label="Recaudo y cumplimiento por grupo"
            tabindex="0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Grupo</th>
                        <th v-if="canViewMonetaryValues" scope="col" class="text-end">Mensualidades</th>
                        <th v-if="canViewMonetaryValues" scope="col" class="text-end">Inscripciones</th>
                        <th scope="col" class="text-end">Cumplimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="`${row.label}-${row.index}`">
                        <th scope="row" class="kpi-collection-table__group">{{ row.label }}</th>
                        <td v-if="canViewMonetaryValues" class="text-end text-nowrap">
                            {{ formatMetricValue(row.monthlyRevenue, 'currency') }}
                        </td>
                        <td v-if="canViewMonetaryValues" class="text-end text-nowrap">
                            {{ formatMetricValue(row.enrollmentRevenue, 'currency') }}
                        </td>
                        <td class="text-end">
                            <div class="kpi-compliance-cell">
                                <span class="kpi-compliance-cell__value">
                                    {{ formatMetricValue(row.compliance, 'percentage') }}
                                </span>
                                <span class="kpi-compliance-cell__track" aria-hidden="true">
                                    <span :style="{ width: `${complianceWidth(row.compliance)}%` }"></span>
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="kpi-collection-table__empty text-muted">
            No hay datos de recaudo para el período seleccionado.
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

import { formatMetricValue } from './kpiFormatters'

const props = defineProps({
    canViewMonetaryValues: { type: Boolean, required: true },
    report: { type: Object, default: () => ({}) },
})

const title = computed(() => props.canViewMonetaryValues
    ? 'Recaudo y cumplimiento por grupo'
    : 'Cumplimiento por grupo')
const description = computed(() => props.canViewMonetaryValues
    ? 'Compara los montos recaudados y el porcentaje de cumplimiento en una sola lectura.'
    : 'Compara el porcentaje de cumplimiento entre los grupos asignados.')

const seriesValues = (name) => props.report?.data?.find((series) => series.name === name)?.data ?? []
const rows = computed(() => (props.report?.categories ?? []).map((label, index) => ({
    index,
    label,
    monthlyRevenue: Number(seriesValues('Mensualidades')[index] ?? 0),
    enrollmentRevenue: Number(seriesValues('Inscripciones')[index] ?? 0),
    compliance: Number(seriesValues('% de cumplimiento')[index] ?? 0),
})))

const formatGroupCount = (count) => `${count} ${count === 1 ? 'grupo' : 'grupos'}`
const complianceWidth = (value) => Math.min(100, Math.max(0, Number(value || 0)))
</script>
