<template>
    <section data-tour="kpi-summary">
        <div class="kpi-content-heading mb-3">
            <p class="kpi-eyebrow mb-1">Panorama general</p>
            <h4 class="mb-1">Resumen ejecutivo</h4>
            <p class="text-muted mb-0">Los indicadores clave del corte seleccionado, reunidos en una sola lectura.</p>
        </div>

        <div class="row g-3">
            <div v-for="card in cards" :key="card.key" class="col-12 col-sm-6 col-xl-4">
                <div :class="['panel br-6 kpi-summary-card', metricPresentation(card.key).tone]">
                    <div class="panel-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <span class="kpi-summary-card__label">{{ card.label }}</span>
                            <span class="kpi-summary-card__icon" aria-hidden="true">
                                <i :class="metricPresentation(card.key).icon"></i>
                            </span>
                        </div>
                        <strong class="kpi-summary-card__value mb-2">
                            {{ formatMetricValue(card.value, card.format) }}
                        </strong>
                        <small class="kpi-summary-card__helper text-muted">{{ card.helper }}</small>

                        <details v-if="card.breakdown" class="kpi-breakdown mt-3">
                            <summary class="small fw-semibold">
                                <span>Cómo se calcula</span>
                                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                            </summary>

                            <div class="small mt-3">
                                <p class="fw-semibold mb-2">Se incluye</p>
                                <ul class="list-unstyled mb-3">
                                    <li
                                        v-for="item in card.breakdown.included"
                                        :key="`${card.key}-included-${item.label}`"
                                        class="d-flex justify-content-between gap-3 mb-2">
                                        <span>{{ item.label }}</span>
                                        <strong>{{ formatMetricValue(item.amount, 'currency') }}</strong>
                                    </li>
                                </ul>

                                <p class="fw-semibold mb-2">Se excluye</p>
                                <ul class="list-unstyled mb-0">
                                    <li
                                        v-for="item in card.breakdown.excluded"
                                        :key="`${card.key}-excluded-${item.label}`"
                                        class="mb-2">
                                        <div class="d-flex justify-content-between gap-3">
                                            <span>{{ item.label }}</span>
                                            <strong v-if="item.amount !== null && item.amount !== undefined">
                                                {{ formatMetricValue(item.amount, 'currency') }}
                                            </strong>
                                        </div>
                                        <span v-if="item.reason" class="text-muted d-block">{{ item.reason }}</span>
                                    </li>
                                </ul>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { formatMetricValue } from './kpiFormatters'

defineProps({
    cards: { type: Array, required: true },
})

const METRIC_PRESENTATIONS = {
    monthly_revenue: { icon: 'fa-solid fa-coins', tone: 'kpi-tone-success' },
    enrollment_revenue: { icon: 'fa-solid fa-file-invoice-dollar', tone: 'kpi-tone-primary' },
    other_billing_revenue: { icon: 'fa-solid fa-cash-register', tone: 'kpi-tone-indigo' },
    payment_compliance: { icon: 'fa-solid fa-gauge-high', tone: 'kpi-tone-info' },
    payments_debt: { icon: 'fa-solid fa-triangle-exclamation', tone: 'kpi-tone-danger' },
    attendance_percentage: { icon: 'fa-solid fa-user-check', tone: 'kpi-tone-warning' },
    flagged_players: { icon: 'fa-solid fa-user-clock', tone: 'kpi-tone-slate' },
}
const DEFAULT_PRESENTATION = { icon: 'fa-solid fa-chart-simple', tone: 'kpi-tone-primary' }

const metricPresentation = (key) => METRIC_PRESENTATIONS[key] ?? DEFAULT_PRESENTATION
</script>
