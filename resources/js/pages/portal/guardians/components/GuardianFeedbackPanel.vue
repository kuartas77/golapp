<template>
    <div class="guardian-feedback">
        <div class="guardian-feedback__heading">
            <div>
                <span class="guardian-feedback__eyebrow">Seguimiento del proceso</span>
                <h2 class="h4 mb-1">Retroalimentación</h2>
                <p class="text-muted mb-0">Observaciones compartidas desde asistencias y competencias.</p>
            </div>
            <span v-if="entries.length" class="guardian-feedback__count">
                {{ entries.length }} registro{{ entries.length === 1 ? '' : 's' }}
            </span>
        </div>

        <div v-if="entries.length" class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="guardian-feedback__timeline" role="tablist" aria-label="Fechas con retroalimentación">
                    <button
                        v-for="entry in entries"
                        :key="entry.id"
                        type="button"
                        class="guardian-feedback__timeline-item"
                        :class="{ 'guardian-feedback__timeline-item--active': entry.id === selectedId }"
                        role="tab"
                        :aria-selected="entry.id === selectedId"
                        @click="selectedId = entry.id"
                    >
                        <span class="guardian-feedback__source" :class="`guardian-feedback__source--${entry.source}`">
                            {{ entry.source_label }}
                        </span>
                        <strong>{{ formatDate(entry.event_date) }}</strong>
                        <span>{{ entrySummary(entry) }}</span>
                    </button>
                </div>
            </div>

            <div v-if="selectedEntry" class="col-12 col-lg-8">
                <article class="guardian-feedback__detail">
                    <header class="guardian-feedback__detail-header">
                        <div>
                            <span class="guardian-feedback__source" :class="`guardian-feedback__source--${selectedEntry.source}`">
                                {{ selectedEntry.source_label }}
                            </span>
                            <h3 class="h5 mb-1 mt-2">{{ detailTitle(selectedEntry) }}</h3>
                            <p class="text-muted mb-0">{{ formatDate(selectedEntry.event_date, true) }}</p>
                        </div>
                        <span v-if="selectedEntry.created_at" class="guardian-feedback__created-at">
                            Registro creado {{ formatDateTime(selectedEntry.created_at) }}
                        </span>
                    </header>

                    <template v-if="selectedEntry.source === 'competition'">
                        <dl class="guardian-feedback__facts">
                            <div v-if="selectedEntry.group_name">
                                <dt>Grupo</dt>
                                <dd>{{ selectedEntry.group_name }}</dd>
                            </div>
                            <div v-if="selectedEntry.tournament_name">
                                <dt>Torneo</dt>
                                <dd>{{ selectedEntry.tournament_name }}</dd>
                            </div>
                            <div v-if="selectedEntry.coach_name">
                                <dt>Director técnico</dt>
                                <dd>{{ selectedEntry.coach_name }}</dd>
                            </div>
                            <div v-if="selectedEntry.match_number">
                                <dt>Partido</dt>
                                <dd>#{{ selectedEntry.match_number }}</dd>
                            </div>
                            <div v-if="selectedEntry.position">
                                <dt>Posición</dt>
                                <dd>{{ selectedEntry.position }}</dd>
                            </div>
                        </dl>

                        <div v-if="selectedEntry.score" class="guardian-feedback__score">
                            <span>{{ selectedEntry.group_name || 'Equipo' }}</span>
                            <strong>{{ scoreValue(selectedEntry.score.team) }} - {{ scoreValue(selectedEntry.score.rival) }}</strong>
                            <span>{{ selectedEntry.rival_name || 'Rival' }}</span>
                        </div>

                        <div class="guardian-feedback__observations">
                            <section v-if="selectedEntry.player_observation" class="guardian-feedback__observation guardian-feedback__observation--player">
                                <span>Para el deportista</span>
                                <p>{{ selectedEntry.player_observation }}</p>
                            </section>
                            <section v-if="selectedEntry.group_observation" class="guardian-feedback__observation">
                                <span>Concepto general del grupo</span>
                                <p>{{ selectedEntry.group_observation }}</p>
                            </section>
                        </div>
                    </template>

                    <template v-else>
                        <dl v-if="selectedEntry.group_name" class="guardian-feedback__facts">
                            <div>
                                <dt>Grupo</dt>
                                <dd>{{ selectedEntry.group_name }}</dd>
                            </div>
                        </dl>

                        <section class="guardian-feedback__observation guardian-feedback__observation--attendance">
                            <span>Observación de la asistencia</span>
                            <p>{{ selectedEntry.observation }}</p>
                        </section>
                    </template>
                </article>
            </div>
        </div>

        <div v-else class="guardian-feedback__empty">
            <span class="guardian-feedback__empty-icon" aria-hidden="true">✓</span>
            <div>
                <strong>Sin observaciones por ahora</strong>
                <p class="text-muted mb-0">Cuando el equipo técnico registre una observación, aparecerá aquí.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    entries: {
        type: Array,
        default: () => [],
    },
});

const selectedId = ref(null);
const selectedEntry = computed(() => props.entries.find((entry) => entry.id === selectedId.value) ?? props.entries[0] ?? null);

watch(() => props.entries, (entries) => {
    if (!entries.some((entry) => entry.id === selectedId.value)) {
        selectedId.value = entries[0]?.id ?? null;
    }
}, { immediate: true });

const compactDateFormatter = new Intl.DateTimeFormat('es-CO', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    timeZone: 'UTC',
});

const fullDateFormatter = new Intl.DateTimeFormat('es-CO', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'UTC',
});

const dateTimeFormatter = new Intl.DateTimeFormat('es-CO', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
});

const parseDate = (value) => {
    if (!value) {
        return null;
    }

    const normalized = /^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T00:00:00Z` : value;
    const date = new Date(normalized);

    return Number.isNaN(date.getTime()) ? null : date;
};

const formatDate = (value, full = false) => {
    const date = parseDate(value);

    if (!date) {
        return value || 'Fecha sin registrar';
    }

    return full ? fullDateFormatter.format(date) : compactDateFormatter.format(date);
};

const formatDateTime = (value) => {
    const date = parseDate(value);
    return date ? dateTimeFormatter.format(date) : value;
};

const entrySummary = (entry) => entry.source === 'competition'
    ? entry.tournament_name || entry.rival_name || 'Partido'
    : entry.group_name || 'Toma de asistencia';

const detailTitle = (entry) => entry.source === 'competition'
    ? `Partido vs. ${entry.rival_name || 'rival'}`
    : 'Observación de asistencia';

const scoreValue = (value) => value ?? '—';
</script>

<style scoped>
.guardian-feedback__heading,
.guardian-feedback__detail-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.guardian-feedback__heading {
    margin-bottom: 1.5rem;
}

.guardian-feedback__eyebrow {
    display: block;
    color: var(--guardian-player-detail-primary);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 0.35rem;
}

.guardian-feedback__count,
.guardian-feedback__source {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 800;
}

.guardian-feedback__count {
    padding: 0.45rem 0.75rem;
    color: var(--guardian-player-detail-primary);
    background: var(--guardian-player-detail-surface-subtle);
}

.guardian-feedback__timeline {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    max-height: 34rem;
    overflow-y: auto;
    padding-right: 0.25rem;
}

.guardian-feedback__timeline-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.3rem;
    width: 100%;
    padding: 0.9rem 1rem;
    text-align: left;
    color: var(--guardian-player-detail-text);
    background: var(--guardian-player-detail-surface-soft);
    border: 1px solid var(--guardian-player-detail-border);
    border-radius: 1rem;
    transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
}

.guardian-feedback__timeline-item > span:last-child {
    color: var(--guardian-player-detail-text-muted);
    font-size: 0.8rem;
}

.guardian-feedback__timeline-item:hover,
.guardian-feedback__timeline-item--active {
    border-color: var(--guardian-player-detail-border-hover);
    background: var(--guardian-player-detail-surface-hover);
}

.guardian-feedback__timeline-item--active {
    box-shadow: inset 3px 0 0 var(--guardian-player-detail-primary);
    transform: translateX(2px);
}

.guardian-feedback__source {
    padding: 0.28rem 0.55rem;
}

.guardian-feedback__source--attendance {
    color: var(--guardian-player-detail-success-text);
    background: var(--guardian-player-detail-success-bg);
}

.guardian-feedback__source--competition {
    color: var(--guardian-player-detail-primary);
    background: var(--guardian-player-detail-surface-subtle);
}

.guardian-feedback__detail {
    min-height: 22rem;
    padding: 1.25rem;
    border: 1px solid var(--guardian-player-detail-border);
    border-radius: 1.25rem;
    background: var(--guardian-player-detail-surface);
    color: var(--guardian-player-detail-text);
}

.guardian-feedback__created-at {
    color: var(--guardian-player-detail-text-muted);
    font-size: 0.78rem;
}

.guardian-feedback__facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    margin: 1.25rem 0;
}

.guardian-feedback__facts > div {
    padding: 0.75rem;
    border-radius: 0.85rem;
    background: var(--guardian-player-detail-surface-soft);
}

.guardian-feedback__facts dt {
    color: var(--guardian-player-detail-text-muted);
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
}

.guardian-feedback__facts dd {
    margin: 0.2rem 0 0;
    font-weight: 700;
}

.guardian-feedback__score {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 1rem;
    color: #fff;
    background: linear-gradient(135deg, var(--guardian-player-detail-primary-strong), var(--guardian-player-detail-primary));
    text-align: center;
}

.guardian-feedback__score span:first-child {
    text-align: right;
}

.guardian-feedback__score span:last-child {
    text-align: left;
}

.guardian-feedback__score strong {
    font-size: 1.35rem;
    white-space: nowrap;
}

.guardian-feedback__observations {
    display: grid;
    gap: 0.85rem;
}

.guardian-feedback__observation {
    padding: 1rem;
    border-radius: 1rem;
    background: var(--guardian-player-detail-surface-soft);
    border-left: 3px solid var(--guardian-player-detail-border-hover);
}

.guardian-feedback__observation--player,
.guardian-feedback__observation--attendance {
    border-left-color: var(--guardian-player-detail-primary);
}

.guardian-feedback__observation span {
    display: block;
    margin-bottom: 0.4rem;
    color: var(--guardian-player-detail-text-muted);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.guardian-feedback__observation p {
    margin: 0;
    line-height: 1.65;
    white-space: pre-line;
}

.guardian-feedback__empty {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border: 1px dashed var(--guardian-player-detail-border-hover);
    border-radius: 1rem;
    color: var(--guardian-player-detail-text);
    background: var(--guardian-player-detail-surface-soft);
}

.guardian-feedback__empty-icon {
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    color: var(--guardian-player-detail-success-text);
    background: var(--guardian-player-detail-success-bg);
    font-weight: 800;
}

@media (max-width: 991.98px) {
    .guardian-feedback__timeline {
        flex-direction: row;
        max-height: none;
        overflow-x: auto;
        padding: 0 0 0.35rem;
    }

    .guardian-feedback__timeline-item {
        flex: 0 0 min(17rem, 82vw);
    }

    .guardian-feedback__timeline-item--active {
        box-shadow: inset 0 -3px 0 var(--guardian-player-detail-primary);
        transform: translateY(-2px);
    }
}

@media (max-width: 575.98px) {
    .guardian-feedback__facts {
        grid-template-columns: 1fr;
    }

    .guardian-feedback__score {
        grid-template-columns: 1fr;
        gap: 0.35rem;
    }

    .guardian-feedback__score span:first-child,
    .guardian-feedback__score span:last-child {
        text-align: center;
    }
}
</style>
