<template>
    <div class="panel br-6 kpi-filter-panel" data-tour="kpi-filters">
        <div class="panel-body kpi-filter-panel__body">
            <div v-if="isLoading && !isReady" class="kpi-loading-state" role="status">
                <span class="spinner-border text-primary" aria-hidden="true"></span>
                <span>Cargando configuración del tablero...</span>
            </div>

            <template v-else-if="isReady">
                <div class="kpi-section-heading mb-4">
                    <span class="kpi-section-heading__icon" aria-hidden="true">
                        <i class="fa-solid fa-sliders"></i>
                    </span>
                    <div>
                        <h5 class="mb-1">Configura el corte</h5>
                        <p class="text-muted mb-0">Ajusta el período y el grupo para actualizar todo el tablero.</p>
                    </div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label kpi-filter-label" for="kpi-year">Año</label>
                        <CustomSelect2
                            id="kpi-year"
                            :model-value="year"
                            :options="years"
                            placeholder="Selecciona un año"
                            @update:model-value="$emit('update:year', $event)" />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label kpi-filter-label" for="kpi-month">Mes</label>
                        <CustomSelect2
                            id="kpi-month"
                            :model-value="month"
                            :options="months"
                            placeholder="Selecciona un mes"
                            @update:model-value="$emit('update:month', $event)" />
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label kpi-filter-label" for="kpi-group">Grupo</label>
                        <CustomSelect2
                            id="kpi-group"
                            :model-value="trainingGroupId"
                            :options="groupOptions"
                            placeholder="Todos los grupos"
                            @update:model-value="$emit('update:trainingGroupId', $event)" />
                    </div>
                    <div class="col-12 col-md-2">
                        <button
                            type="button"
                            class="btn btn-primary w-100 kpi-update-button"
                            :disabled="isLoading"
                            @click="$emit('apply')">
                            <i v-if="!isLoading" class="fa-solid fa-rotate me-2" aria-hidden="true"></i>
                            <span v-else class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                            {{ isLoading ? 'Actualizando...' : 'Actualizar' }}
                        </button>
                    </div>
                </div>

                <div class="kpi-filter-footer d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mt-4">
                    <small class="kpi-period-chip">
                        <i class="fa-regular fa-calendar me-2" aria-hidden="true"></i>
                        Corte actual: {{ selectedMonthLabel }} de {{ year || '—' }}
                    </small>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="btn btn-outline-secondary btn-sm kpi-clear-button"
                        @click="$emit('reset')">
                        <i class="fa-solid fa-filter-circle-xmark me-2" aria-hidden="true"></i>
                        Limpiar filtros
                    </button>
                </div>
            </template>

            <div v-else class="alert alert-danger d-flex align-items-center gap-3 mb-0">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                <span>{{ loadError || 'No fue posible cargar la configuración del tablero.' }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    groupOptions: { type: Array, required: true },
    hasActiveFilters: { type: Boolean, required: true },
    isLoading: { type: Boolean, required: true },
    isReady: { type: Boolean, required: true },
    loadError: { type: String, default: null },
    month: { type: [Number, String], default: null },
    months: { type: Array, required: true },
    selectedMonthLabel: { type: String, required: true },
    trainingGroupId: { type: [Number, String], default: null },
    year: { type: [Number, String], default: null },
    years: { type: Array, required: true },
})

defineEmits(['apply', 'reset', 'update:month', 'update:trainingGroupId', 'update:year'])
</script>
