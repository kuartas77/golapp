<template>
    <div class="group-assignment-board">
        <div class="row g-3 mb-4">
            <template v-if="isTraining">
                <div class="col-lg-6">
                    <label class="form-label form-label-sm" for="origin_group_id">Grupo de origen</label>
                    <select
                        id="origin_group_id"
                        v-model="selectedOriginGroupId"
                        class="form-select form-select-sm"
                        @change="onOriginChange"
                    >
                        <option value="">Selecciona...</option>
                        <option
                            v-for="group in selectors.origin_groups"
                            :key="group.value"
                            :value="group.value"
                        >
                            {{ group.label }}
                        </option>
                    </select>
                </div>

                <div class="col-lg-6">
                    <label class="form-label form-label-sm" for="destination_group_id">Grupo de destino</label>
                    <select
                        id="destination_group_id"
                        v-model="selectedDestinationGroupId"
                        class="form-select form-select-sm"
                        @change="onDestinationChange"
                    >
                        <option value="">Selecciona...</option>
                        <option
                            v-for="group in selectors.destination_groups"
                            :key="group.value"
                            :value="group.value"
                        >
                            {{ group.label }}
                        </option>
                    </select>
                </div>
            </template>

            <template v-else>
                <div class="col-lg-8">
                    <label class="form-label form-label-sm" for="competition_group_id">Grupo de competencia</label>
                    <select
                        id="competition_group_id"
                        v-model="selectedCompetitionGroupId"
                        class="form-select form-select-sm"
                        @change="onCompetitionGroupChange"
                    >
                        <option value="">Selecciona...</option>
                        <option
                            v-for="group in selectors.groups"
                            :key="group.value"
                            :value="group.value"
                        >
                            {{ group.label }}
                        </option>
                    </select>
                </div>

                <div class="col-lg-4 d-flex align-items-end">
                    <div class="small text-muted">
                        Selecciona un grupo para cargar sus integrantes y arrastrar deportistas entre paneles.
                    </div>
                </div>
            </template>
        </div>

        <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3" role="alert">
            <span>{{ globalError }}</span>
            <button type="button" class="btn btn-outline-danger btn-sm align-self-start" @click="loadBoard">
                Reintentar
            </button>
        </div>

        <div class="position-relative">
            <Loader :is-loading="isLoading" loading-text="Cargando tablero..." />

            <div class="row g-4">
                <div class="col-xl-6 col-lg-6" data-tour="group-assignment-source">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                <div>
                                    <h6 class="mb-1">{{ sourceTitle }}</h6>
                                    <small class="text-muted">{{ sourceSubtitle }}</small>
                                </div>
                                <span class="badge bg-info text-body-secondary border">
                                    Cantidad: {{ sourcePanel.count }}
                                </span>
                            </div>

                            <div v-if="showSearch" class="mb-3">
                                <input
                                    v-model="sourceSearch"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Buscar..."
                                    autocomplete="off"
                                >
                            </div>

                            <draggable
                                class="assignment-list"
                                :list="sourceItems"
                                :sort="false"
                                group="group-assignment-board"
                                item-key="client_key"
                                data-panel="source"
                                ghost-class="assignment-card--ghost"
                                @start="onDragStart"
                                @end="onDragEnd"
                            >
                                <div
                                    v-for="element in sourceItems"
                                    :key="element.client_key"
                                    v-show="matchesSourceSearch(element)"
                                    class="assignment-list__item"
                                    :data-inscription-id="element.id"
                                >
                                    <article class="card border shadow-sm h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <img
                                                    :src="element.photo_url"
                                                    :alt="element.full_names"
                                                    class="assignment-avatar rounded-circle"
                                                >
                                                <div class="min-w-0">
                                                    <h6 class="mb-1 text-truncate">{{ element.full_names }}</h6>
                                                    <p class="mb-0 small text-muted text-truncate">
                                                        Categoría: {{ element.category || 'Sin categoría' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            </draggable>

                            <div v-if="!sourceItems.length" class="assignment-empty text-muted">
                                {{ sourceEmptyMessage }}
                            </div>
                            <div v-else-if="showSearch && sourceVisibleCount === 0" class="assignment-empty text-muted">
                                No hay resultados para esta búsqueda.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-6" data-tour="group-assignment-destination">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                <div>
                                    <h6 class="mb-1">{{ destinationTitle }}</h6>
                                    <small class="text-muted">{{ destinationSubtitle }}</small>
                                </div>
                                <span class="badge bg-info text-body-secondary border">
                                    Cantidad: {{ destinationPanel.count }}
                                </span>
                            </div>

                            <div v-if="showSearch" class="mb-3">
                                <input
                                    v-model="destinationSearch"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Buscar..."
                                    autocomplete="off"
                                >
                            </div>

                            <draggable
                                class="assignment-list"
                                :list="destinationItems"
                                :sort="false"
                                group="group-assignment-board"
                                item-key="client_key"
                                data-panel="destination"
                                ghost-class="assignment-card--ghost"
                                @start="onDragStart"
                                @end="onDragEnd"
                            >
                                <div
                                    v-for="element in destinationItems"
                                    :key="element.client_key"
                                    v-show="matchesDestinationSearch(element)"
                                    class="assignment-list__item"
                                    :data-inscription-id="element.id"
                                >
                                    <article class="card border shadow-sm h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <img
                                                    :src="element.photo_url"
                                                    :alt="element.full_names"
                                                    class="assignment-avatar rounded-circle"
                                                >
                                                <div class="min-w-0">
                                                    <h6 class="mb-1 text-truncate">{{ element.full_names }}</h6>
                                                    <p class="mb-0 small text-muted text-truncate">
                                                        Categoría: {{ element.category || 'Sin categoría' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            </draggable>

                            <div v-if="!destinationItems.length" class="assignment-empty text-muted">
                                {{ destinationEmptyMessage }}
                            </div>
                            <div v-else-if="showSearch && destinationVisibleCount === 0" class="assignment-empty text-muted">
                                No hay resultados para esta búsqueda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { computed } from 'vue'
import Loader from '@/components/general/Loader.vue'
import { VueDraggableNext as draggable } from 'vue-draggable-next'
import useGroupAssignmentBoard from '@/composables/admin/groups/useGroupAssignmentBoard'

const props = defineProps({
    mode: {
        type: String,
        required: true,
        validator: (value) => ['training', 'competition'].includes(value),
    },
})

const {
    destinationItems,
    destinationPanel,
    destinationSearch,
    destinationVisibleCount,
    globalError,
    isLoading,
    isTraining,
    loadBoard,
    onCompetitionGroupChange,
    onDestinationChange,
    onDragEnd,
    onDragStart,
    onOriginChange,
    selectedCompetitionGroupId,
    selectedDestinationGroupId,
    selectedDestinationLabel,
    selectedOriginGroupId,
    selectors,
    showSearch,
    sourceItems,
    sourcePanel,
    sourceSearch,
    sourceVisibleCount,
} = useGroupAssignmentBoard(props.mode)

const sourceTitle = computed(() => (
    isTraining.value ? 'Grupo de origen' : 'Deportistas'
))

const sourceSubtitle = computed(() => (
    isTraining.value
        ? (sourcePanel.value.group_label || 'Selecciona un grupo de origen para ver sus integrantes.')
        : 'Deportistas del año actual.'
))

const destinationTitle = computed(() => (
    isTraining.value ? 'Grupo de destino' : 'Grupo seleccionado'
))

const destinationSubtitle = computed(() => (
    isTraining.value
        ? (destinationPanel.value.group_label || 'Selecciona un grupo de destino para recibir integrantes.')
        : selectedDestinationLabel.value
))

const sourceEmptyMessage = computed(() => (
    isTraining.value
        ? 'No hay integrantes para este grupo de origen.'
        : 'No hay deportistas disponibles para mostrar.'
))

const destinationEmptyMessage = computed(() => (
    isTraining.value
        ? 'No hay integrantes en este grupo de destino.'
        : 'Este grupo no tiene integrantes asignados.'
))

const matchesSourceSearch = (item) => matchesSearch(item, sourceSearch.value)
const matchesDestinationSearch = (item) => matchesSearch(item, destinationSearch.value)

function matchesSearch(item, query) {
    const normalizedQuery = String(query || '').trim().toLowerCase()

    if (!normalizedQuery) {
        return true
    }

    return String(item.search_text || '').toLowerCase().includes(normalizedQuery)
}
</script>
<style scoped>
.group-assignment-board {
    position: relative;
}

.assignment-list {
    min-height: 20rem;
    max-height: 34rem;
    overflow-y: auto;
    border: 1px dashed rgba(127, 127, 127, 0.35);
    border-radius: 0.5rem;
    padding: 0.75rem;
}

.assignment-list__item + .assignment-list__item {
    margin-top: 0.75rem;
}

.assignment-avatar {
    width: 3rem;
    height: 3rem;
    object-fit: cover;
    flex-shrink: 0;
}

.assignment-empty {
    min-height: 8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 1rem;
}

.assignment-card--ghost {
    opacity: 0.45;
}

.min-w-0 {
    min-width: 0;
}
</style>
