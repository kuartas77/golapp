<template>
    <div class="match-table-toolbar no-print" data-tour="match-form-stats">
        <div>
            <h4 class="match-table-title">Jugadores</h4>
            <small class="text-muted">
                Actualiza minutos, desempeño y observaciones del partido.
            </small>
        </div>
        <div class="match-table-actions">
            <div class="match-player-filter">
                <CustomSelect2
                    id="match-player-filter"
                    v-model="selectedPlayerIndex"
                    :options="playerOptions"
                    placeholder="Todos los jugadores"
                    search-placeholder="Buscar jugador..."
                    aria-label="Filtrar jugadores"
                    :clearable="false"
                />
            </div>
            <span class="match-table-count" aria-live="polite">
                <template v-if="hasPlayerFilter">{{ visiblePlayersCount }} de </template>
                <span v-else>Total&nbsp;</span><strong>{{ skillsControls.length }}</strong>
            </span>
        </div>
    </div>

    <div class="match-table-wrapper table-responsive no-print">
        <table class="table table-sm dataTable align-middle match-table">
            <thead>
                <tr>
                    <th scope="col">Deportista</th>
                    <th scope="col">Participación</th>
                    <th scope="col">Posición</th>
                    <th scope="col">Rendimiento</th>
                    <th scope="col">Disciplina</th>
                    <th scope="col">Evaluación</th>
                </tr>
            </thead>
            <tbody>
                <template v-if="skillsControls.length">
                    <tr v-for="(skillControl, index) in skillsControls"
                        :key="skillControl.id ?? skillControl.player?.id ?? index"
                        v-show="visiblePlayerIndexes.has(index)"
                        class="match-player-row">
                        <td class="match-player-cell" data-section="Deportista">
                            <div class="match-player-meta">
                                <img :src="skillControl.player?.photo_url || '/img/user.webp'"
                                    :alt="`Foto de ${skillControl.player?.full_names || 'jugador'}`"
                                    class="player-avatar" />
                                <div class="match-player-identity">
                                    <span class="match-player-name">
                                        {{ skillControl.player?.full_names || 'Jugador sin datos' }}
                                    </span>
                                </div>
                            </div>
                            <div class="match-player-details">
                                <span class="match-player-code">
                                    {{ skillControl.player?.unique_code || 'Sin código' }}
                                </span>
                                <span v-if="skillControl.is_retired_player"
                                    class="badge bg-warning text-dark match-player-retired-badge">
                                    Jugador retirado
                                </span>
                            </div>
                        </td>

                        <td class="match-group-cell match-participation-cell" data-section="Participación">
                            <div class="match-field-grid match-field-grid--participation">
                                <div class="match-boolean-field">
                                    <Checkbox :name="`skill_controls[${index}].assistance`"
                                        label="Asistió"
                                        return-value-type="number" />
                                </div>
                                <div class="match-boolean-field">
                                    <Checkbox :name="`skill_controls[${index}].titular`"
                                        label="Titular"
                                        return-value-type="number" />
                                </div>
                                <div class="match-field match-field--minutes">
                                    <label :for="`skill_controls[${index}].played_approx`">Minutos</label>
                                    <Field :name="`skill_controls[${index}].played_approx`"
                                        v-slot="{ field, errorMessage, meta }">
                                        <select v-bind="field"
                                            :id="`skill_controls[${index}].played_approx`"
                                            class="form-select form-select-sm"
                                            :class="{ 'is-invalid': meta.touched && errorMessage }">
                                            <option :value="minute - 1" v-for="minute in 91"
                                                :key="`${minute - 1}_${index}`">{{ minute - 1 }} min</option>
                                        </select>
                                    </Field>
                                    <ErrorMessage :name="`skill_controls[${index}].played_approx`"
                                        class="invalid-feedback d-block" />
                                </div>
                            </div>
                        </td>

                        <td class="match-group-cell match-position-cell" data-section="Posición">
                            <div class="match-field">
                                <label :for="`skill_controls[${index}].position`">Posición</label>
                                <Field :name="`skill_controls[${index}].position`"
                                    v-slot="{ field, errorMessage, meta }">
                                    <select v-bind="field"
                                        :id="`skill_controls[${index}].position`"
                                        class="form-select form-select-sm"
                                        :class="{ 'is-invalid': meta.touched && errorMessage }">
                                        <option value="">Selecciona...</option>
                                        <option v-for="position in positionOptions"
                                            :key="position.value"
                                            :value="position.value">
                                            {{ position.label }}
                                        </option>
                                    </select>
                                </Field>
                                <ErrorMessage :name="`skill_controls[${index}].position`"
                                    class="invalid-feedback d-block" />
                            </div>
                        </td>

                        <td class="match-group-cell match-performance-cell" data-section="Rendimiento">
                            <div class="match-field-grid match-field-grid--performance">
                                <div class="match-field">
                                    <label :for="`skill_controls[${index}].goals`">Goles</label>
                                    <Field :name="`skill_controls[${index}].goals`" as="select"
                                        :id="`skill_controls[${index}].goals`"
                                        class="form-select form-select-sm">
                                        <option v-for="score in 11" :key="score - 1"
                                            :value="String(score - 1)">{{ score - 1 }}</option>
                                    </Field>
                                    <ErrorMessage :name="`skill_controls[${index}].goals`"
                                        class="invalid-feedback d-block" />
                                </div>
                                <div class="match-field">
                                    <label :for="`skill_controls[${index}].goal_assists`">Asist. gol</label>
                                    <Field :name="`skill_controls[${index}].goal_assists`" as="select"
                                        :id="`skill_controls[${index}].goal_assists`"
                                        class="form-select form-select-sm">
                                        <option v-for="score in 11" :key="score - 1"
                                            :value="String(score - 1)">{{ score - 1 }}</option>
                                    </Field>
                                    <ErrorMessage :name="`skill_controls[${index}].goal_assists`"
                                        class="invalid-feedback d-block" />
                                </div>
                                <div class="match-field match-field--full">
                                    <label :for="`skill_controls[${index}].goal_saves`">Atajadas</label>
                                    <Field :name="`skill_controls[${index}].goal_saves`" as="select"
                                        :id="`skill_controls[${index}].goal_saves`"
                                        class="form-select form-select-sm">
                                        <option v-for="score in 11" :key="score - 1"
                                            :value="String(score - 1)">{{ score - 1 }}</option>
                                    </Field>
                                    <ErrorMessage :name="`skill_controls[${index}].goal_saves`"
                                        class="invalid-feedback d-block" />
                                </div>
                            </div>
                        </td>

                        <td class="match-group-cell match-discipline-cell" data-section="Disciplina">
                            <div class="match-field-grid match-field-grid--stacked">
                                <div class="match-field">
                                    <label :for="`skill_controls[${index}].yellow_cards`">🟨 Amarillas</label>
                                    <Field :name="`skill_controls[${index}].yellow_cards`" as="select"
                                        :id="`skill_controls[${index}].yellow_cards`"
                                        class="form-select form-select-sm">
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                    </Field>
                                    <ErrorMessage :name="`skill_controls[${index}].yellow_cards`"
                                        class="invalid-feedback d-block" />
                                </div>
                                <div class="match-field">
                                    <label :for="`skill_controls[${index}].red_cards`">🟥 Rojas</label>
                                    <Field :name="`skill_controls[${index}].red_cards`" as="select"
                                        :id="`skill_controls[${index}].red_cards`"
                                        class="form-select form-select-sm">
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                    </Field>
                                    <ErrorMessage :name="`skill_controls[${index}].red_cards`"
                                        class="invalid-feedback d-block" />
                                </div>
                            </div>
                        </td>

                        <td class="match-group-cell match-evaluation-cell" data-section="Evaluación">
                            <div class="match-evaluation-layout">
                                <div class="match-field match-rating-field">
                                    <label :for="`skill_controls[${index}].qualification`">Calificación</label>
                                    <Field :name="`skill_controls[${index}].qualification`" as="select"
                                        :id="`skill_controls[${index}].qualification`"
                                        class="form-select form-select-sm">
                                        <option value="">Selecciona...</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </Field>
                                    <ErrorMessage :name="`skill_controls[${index}].qualification`"
                                        class="invalid-feedback d-block" />
                                </div>
                                <div class="match-field match-observation-field-wrapper">
                                    <label :for="`skill_controls[${index}].observation`">Observación</label>
                                    <Field :name="`skill_controls[${index}].observation`"
                                        :id="`skill_controls[${index}].observation`" as="textarea"
                                        class="form-control form-control-sm match-observation-field"
                                        rows="1"
                                        placeholder="Escribe una observación" />
                                    <ErrorMessage :name="`skill_controls[${index}].observation`"
                                        class="invalid-feedback d-block" />
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <tr class="match-empty-row">
                        <td colspan="6" class="dt-body-center">
                            El grupo no cuenta con integrantes
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import Checkbox from '@/components/form/Checkbox.vue'
import CustomSelect2 from '@/components/form/CustomSelect2.vue'
import { ErrorMessage, Field } from 'vee-validate'
import { computed, ref } from 'vue'

const props = defineProps({
    skillsControls: {
        type: Array,
        default: () => [],
    },
    positionOptions: {
        type: Array,
        default: () => [],
    },
})

const ALL_PLAYERS = 'all'
const selectedPlayerIndex = ref(ALL_PLAYERS)
const playerOptions = computed(() => [
    { value: ALL_PLAYERS, label: 'Todos los jugadores' },
    ...props.skillsControls.map((skillControl, index) => {
        const name = skillControl.player?.full_names || 'Jugador sin datos'
        const code = skillControl.player?.unique_code || 'Sin código'

        return {
            value: String(index),
            label: `${name} · ${code}`,
            meta: code,
        }
    }),
])
const hasPlayerFilter = computed(() => selectedPlayerIndex.value !== ALL_PLAYERS)
const visiblePlayerIndexes = computed(() => hasPlayerFilter.value
    ? new Set([Number(selectedPlayerIndex.value)])
    : new Set(props.skillsControls.map((_, index) => index)))
const visiblePlayersCount = computed(() => visiblePlayerIndexes.value.size)
</script>

<style lang="scss" scoped>
.match-table-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.match-table-title {
    margin-bottom: 0.2rem;
    font-size: 1rem;
    font-weight: 700;
}

.match-table-count {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: var(--match-surface-strong);
    font-size: 0.78rem;
    font-weight: 700;
}

.match-table-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    align-items: center;
    justify-content: flex-end;
}

.match-player-filter {
    min-width: min(250px, 70vw);
}

.match-table-wrapper {
    border: 1px solid var(--match-border);
    border-radius: 0.9rem;
    overflow: auto;
}

.match-table {
    min-width: 980px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.match-table thead th {
    padding: 0.75rem 0.6rem;
    border-top: 0;
    border-color: var(--match-border);
    white-space: nowrap;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--match-muted);
    background: var(--match-surface-soft);
}

.match-table td {
    padding: 0.75rem 0.55rem;
    border-color: var(--match-border);
    vertical-align: top;
    background: var(--match-surface);
}

.match-table tbody tr:nth-child(even) td {
    background: var(--match-surface-soft);
}

.match-table thead th:first-child,
.match-player-cell {
    position: sticky;
    left: 0;
    z-index: 2;
}

.match-table thead th:first-child {
    z-index: 3;
}

.match-player-cell {
    width: 15%;
    min-width: 160px;
}

.match-player-meta {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    min-width: 140px;
}

.match-player-identity {
    min-width: 0;
}

.match-player-name {
    display: block;
    font-weight: 600;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.match-player-details {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
    margin-top: 0.55rem;
}

.match-player-code {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    background: var(--match-surface-strong);
    font-size: 0.72rem;
    line-height: 1.2;
}

.match-player-retired-badge {
    display: inline-flex;
    font-size: 0.68rem;
    line-height: 1.2;
}

.match-group-cell {
    min-width: 0;
}

.match-participation-cell {
    width: 17%;
}

.match-position-cell {
    width: 17%;
    min-width: 165px;
}

.match-performance-cell {
    width: 15%;
    min-width: 150px;
}

.match-discipline-cell {
    width: 10%;
    min-width: 105px;
}

.match-evaluation-cell {
    width: 26%;
    min-width: 230px;
}

.match-field-grid {
    display: grid;
    gap: 0.55rem;
    align-items: start;
}

.match-field-grid--participation {
    grid-template-columns: repeat(2, minmax(55px, 1fr));
}

.match-field-grid--performance {
    grid-template-columns: repeat(2, minmax(62px, 1fr));
}

.match-field-grid--stacked {
    grid-template-columns: minmax(0, 1fr);
}

.match-field--minutes,
.match-field--full {
    grid-column: 1 / -1;
}

.match-field label,
.match-boolean-field :deep(.custom-control-label) {
    display: block;
    margin-bottom: 0.3rem;
    color: var(--match-muted);
    font-size: 0.68rem;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
}

.match-boolean-field {
    display: flex;
    min-height: 2.1rem;
    align-items: center;
    padding: 0.4rem 0.5rem;
    border: 1px solid var(--match-border);
    border-radius: 0.55rem;
    background: var(--match-surface-soft);
}

.match-boolean-field :deep(.form-group),
.match-boolean-field :deep(.form-check),
.match-boolean-field :deep(.custom-control-label) {
    margin-bottom: 0;
}

.match-boolean-field :deep(.custom-control) {
    min-height: 1.2rem;
}

.match-evaluation-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0.55rem;
    align-items: start;
}

.match-observation-field {
    min-width: 0;
    min-height: 31px;
    max-height: 8rem;
    resize: vertical;
    transition: min-height 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
}

.match-observation-field:focus {
    min-height: 76px;
}

.match-table :deep(.form-control-sm),
.match-table :deep(.form-select-sm) {
    min-width: 0;
    border-color: var(--match-border);
    background-color: var(--match-surface);
    color: inherit;
}

.match-table :deep(.form-control-sm:focus),
.match-table :deep(.form-select-sm:focus),
.match-boolean-field:focus-within {
    border-color: var(--bs-info, #2196f3);
    box-shadow: 0 0 0 0.16rem rgba(33, 150, 243, 0.2);
}

.match-empty-row td {
    padding: 2rem 1rem;
    color: var(--match-muted);
}

@media (max-width: 767.98px) {
    .match-table-wrapper {
        border: 0;
        border-radius: 0;
        overflow: visible;
    }

    .match-table,
    .match-table tbody {
        display: block;
        width: 100%;
        min-width: 0;
    }

    .match-table thead {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .match-player-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0;
        margin-bottom: 1rem;
        border: 1px solid var(--match-border);
        border-radius: 0.9rem;
        overflow: hidden;
        background: var(--match-surface);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    :global(body.dark .match-player-row) {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .match-table .match-player-row td {
        display: block;
        width: auto;
        min-width: 0;
        padding: 0.85rem;
        border: 0;
        border-bottom: 1px solid var(--match-border);
        background: var(--match-surface);
    }

    .match-table .match-player-row td:nth-child(even) {
        border-left: 1px solid var(--match-border);
    }

    .match-table .match-player-row td::before {
        content: attr(data-section);
        display: block;
        margin-bottom: 0.65rem;
        color: var(--match-muted);
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .match-table .match-player-cell {
        position: static;
        grid-column: 1 / -1;
        border-left: 0;
        background: var(--match-surface-soft);
    }

    .match-table .match-player-cell::before {
        display: none;
    }

    .match-table .match-evaluation-cell {
        grid-column: 1 / -1;
        border-bottom: 0;
    }

    .match-empty-row,
    .match-empty-row td {
        display: block;
        width: 100%;
    }
}

@media (max-width: 575.98px) {
    .match-table-actions,
    .match-player-filter {
        width: 100%;
    }

    .match-table-count {
        margin-left: auto;
    }

    .match-player-row {
        grid-template-columns: minmax(0, 1fr);
    }

    .match-table .match-player-row td,
    .match-table .match-player-row td:nth-child(even) {
        grid-column: 1;
        border-left: 0;
    }

    .match-table .match-player-row td:last-child {
        border-bottom: 0;
    }
}
</style>
