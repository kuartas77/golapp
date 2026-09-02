<template>
    <div class="layout-px-spacing kpi-dashboard">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <KpiDashboardHeader
                    :is-loading="isLoading"
                    :is-ready="isReady"
                    @start-tutorial="tutorial.start()" />
            </div>
        </div>

        <div class="row layout-top-spacing">
            <div class="col-12">
                <KpiFiltersPanel
                    :group-options="groupOptions"
                    :has-active-filters="hasActiveFilters"
                    :is-loading="isLoading"
                    :is-ready="isReady"
                    :load-error="loadError"
                    :months="months"
                    :selected-month-label="selectedMonthLabel"
                    :years="years"
                    v-model:month="filters.month"
                    v-model:training-group-id="filters.training_group_id"
                    v-model:year="filters.year"
                    @apply="applyFilters"
                    @reset="resetFilters" />
            </div>
        </div>

        <div v-if="loadError && isReady" class="row layout-top-spacing">
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-center gap-3 mb-0">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span>{{ loadError }}</span>
                </div>
            </div>
        </div>

        <KpiSummaryCards v-if="isReady" class="mt-4" :cards="summaryCards" />

        <div v-if="isReady && canOpenReports" class="row layout-top-spacing">
            <div class="col-12">
                <KpiReportLinks :links="reportLinks" />
            </div>
        </div>

        <template v-if="isReady">
            <KpiCharts
                class="mt-4"
                :amount-payment-group-report="amountPaymentGroupReport"
                :attendance-mix-report="attendanceMixReport"
                :can-view-monetary-values="canViewMonetaryValues"
                :monthly-trend-report="monthlyTrendReport"
                :payment-group-report="paymentGroupReport" />
            <KpiRankings class="mt-3" :rankings="rankings" />
        </template>
    </div>

    <PageTutorialOverlay :tutorial="tutorial" />
</template>

<script>
export default {
    name: 'kpi-dashboard-index',
}
</script>

<script setup>
import { computed } from 'vue'

import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import KpiCharts from '@/components/kpi/KpiCharts.vue'
import KpiDashboardHeader from '@/components/kpi/KpiDashboardHeader.vue'
import KpiFiltersPanel from '@/components/kpi/KpiFiltersPanel.vue'
import KpiRankings from '@/components/kpi/KpiRankings.vue'
import KpiReportLinks from '@/components/kpi/KpiReportLinks.vue'
import KpiSummaryCards from '@/components/kpi/KpiSummaryCards.vue'
import useKpiDashboard from '@/composables/kpi/useKpiDashboard'

import '@/assets/sass/widgets/widgets.scss'
import '@/components/kpi/kpi-dashboard.scss'

const {
    amountPaymentGroupReport,
    applyFilters,
    attendanceMixReport,
    canOpenReports,
    canViewMonetaryValues,
    filters,
    groupOptions,
    hasActiveFilters,
    isLoading,
    isReady,
    loadError,
    monthlyTrendReport,
    months,
    paymentGroupReport,
    rankings,
    reportLinks,
    resetFilters,
    summaryCards,
    tutorial,
    years,
} = useKpiDashboard()

const selectedMonthLabel = computed(() =>
    months.value.find((monthOption) => monthOption.value === filters.month)?.label || 'Mes'
)
</script>
