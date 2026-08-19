<template>
    <article
        class="attendance-player-card"
        :class="{ 'attendance-player-card--readonly': readOnly }"
        :data-status="attendanceValue || 'pending'"
        role="listitem"
    >
        <div class="attendance-player-card__identity">
            <div class="avatar avatar-sm attendance-player-card__avatar">
                <img
                    :src="row.inscription.player.photo_url"
                    :alt="`Foto de ${row.inscription.player.full_names}`"
                    class="player-avatar"
                />
            </div>
            <div class="attendance-player-card__details">
                <div class="attendance-player-card__heading">
                    <span class="attendance-player-card__name">
                        {{ row.inscription.player.full_names }}
                    </span>
                    <span v-if="row.inscription_deleted" class="badge bg-warning text-dark">
                        Inscripción retirada
                    </span>
                    <span v-if="row.period_locked" class="badge bg-secondary">
                        Periodo cerrado
                    </span>
                </div>
                <div class="attendance-player-card__meta">
                    <span>{{ row.inscription.player.unique_code }}</span>
                    <span aria-hidden="true">•</span>
                    <span>{{ row.inscription.player.category }}</span>
                </div>
            </div>
        </div>

        <div class="attendance-player-card__actions">
            <div class="attendance-player-card__status">
                <label class="attendance-player-card__label" :for="statusFieldId">
                    Estado de asistencia
                </label>
                <select
                    :id="statusFieldId"
                    class="form-select form-select-sm"
                    :value="attendanceValue"
                    :disabled="readOnly"
                    data-tour="attendance-status-select"
                    @change="$emit('attendance-change', $event.target.value)"
                >
                    <option value="">Selecciona...</option>
                    <option
                        v-for="(label, value) in attendanceTypes"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </div>
            <button
                type="button"
                class="btn btn-outline-primary btn-sm attendance-player-card__observation"
                data-tour="attendance-observation-button"
                :disabled="readOnly"
                @click="$emit('open-observation')"
            >
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <span>Observación</span>
            </button>
        </div>
    </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    row: {
        type: Object,
        required: true,
    },
    attendanceTypes: {
        type: Object,
        required: true,
    },
    attendanceValue: {
        type: [String, Number],
        default: '',
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['attendance-change', 'open-observation'])

const statusFieldId = computed(() => `attendance-status-${props.row.id}`)
</script>

<style scoped>
.attendance-player-card {
    --attendance-status-color: #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1rem;
    border: 1px solid #e2e8f0;
    border-left: 4px solid var(--attendance-status-color);
    border-radius: 0.8rem;
    background: #fff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
}

.attendance-player-card:hover {
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.09);
    transform: translateY(-1px);
}

.attendance-player-card[data-status="1"] {
    --attendance-status-color: #22c55e;
}

.attendance-player-card[data-status="2"] {
    --attendance-status-color: #ef4444;
}

.attendance-player-card[data-status="3"] {
    --attendance-status-color: #f59e0b;
}

.attendance-player-card[data-status="4"],
.attendance-player-card[data-status="5"] {
    --attendance-status-color: #64748b;
}

.attendance-player-card--readonly {
    background: #f8fafc;
}

.attendance-player-card__identity {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    min-width: 0;
}

.attendance-player-card__avatar {
    flex: 0 0 auto;
    margin: 0;
}

.attendance-player-card__details {
    min-width: 0;
}

.attendance-player-card__heading {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
}

.attendance-player-card__name {
    overflow: hidden;
    font-size: 0.9rem;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.attendance-player-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.2rem;
    color: #64748b;
    font-size: 0.75rem;
}

.attendance-player-card__actions {
    display: flex;
    align-items: flex-end;
    gap: 0.75rem;
    flex: 0 0 auto;
}

.attendance-player-card__status {
    width: 13rem;
}

.attendance-player-card__label {
    display: block;
    margin-bottom: 0.25rem;
    color: #64748b;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.attendance-player-card__observation {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    min-height: 2rem;
    white-space: nowrap;
}

:global(body.dark) .attendance-player-card__meta,
:global(body.dark) .attendance-player-card__label {
    color: #bfc9d4;
}

:global(body.dark) .attendance-player-card {
    border-color: #2b3c52;
    border-left-color: var(--attendance-status-color);
    background: #172235;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
}

:global(body.dark) .attendance-player-card:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.28);
}

:global(body.dark) .attendance-player-card--readonly {
    background: #111b2c;
}

@media (max-width: 767.98px) {
    .attendance-player-card {
        align-items: stretch;
        flex-direction: column;
        padding: 0.85rem;
    }

    .attendance-player-card__name {
        white-space: normal;
    }

    .attendance-player-card__actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        padding-top: 0.75rem;
        border-top: 1px solid #e2e8f0;
    }

    .attendance-player-card__status {
        width: auto;
    }

    :global(body.dark) .attendance-player-card__actions {
        border-top-color: #2b3c52;
    }
}

@media (max-width: 420px) {
    .attendance-player-card__actions {
        grid-template-columns: 1fr;
    }

    .attendance-player-card__observation {
        width: 100%;
    }
}
</style>
