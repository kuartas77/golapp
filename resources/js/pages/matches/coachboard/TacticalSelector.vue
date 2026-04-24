<template>
    <section class="card border-0 shadow-sm no-print tactical-selector" :class="{ 'tactical-selector--compact': compact }">
        <div class="card-body" :class="compact ? 'p-2 p-md-3' : 'p-3'">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3 selector-head">
                <div>
                    <div class="small text-muted text-uppercase fw-semibold mb-1">
                        {{ compact ? 'Sistema táctico' : 'Configuración táctica' }}
                    </div>
                    <h3 class="mb-0" :class="compact ? 'h6 selector-title-compact' : 'h5'">
                        {{ compact ? 'Formación' : 'Sistema de juego' }}
                    </h3>
                </div>
                <span class="badge rounded-pill text-bg-primary selector-badge" :class="compact ? 'px-2 py-1' : 'px-3 py-2'">
                    {{ selectedFormation }}
                </span>
            </div>

            <div class="selector-grid" :class="{ 'selector-grid--compact': compact }">
                <div class="form-group selector-item">
                    <label class="form-label selector-label" :for="modalityId">Modalidad</label>
                    <select class="form-select form-select-sm" v-model="modalitySelected" @change="emitChange" :id="modalityId">
                        <option v-for="(label, key) in footballModality" :key="key" :value="parseInt(key)">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <div class="form-group selector-item">
                    <label class="form-label selector-label" :for="systemId">Sistema</label>
                    <select class="form-select form-select-sm" v-model="selectedFormation" @change="emitChange" :id="systemId">
                        <option v-for="formation in availableFormations" :key="formation" :value="formation">
                            {{ formation }}
                        </option>
                    </select>
                </div>

                <div class="form-group selector-item selector-item--grow">
                    <label class="form-label selector-label" :for="newSystemId">Nuevo</label>
                    <input
                        v-model="newFormation"
                        placeholder="Ej: 4-3-3"
                        class="form-control form-control-sm"
                        :id="newSystemId"
                        @keyup.enter="tryAdd"
                    />
                    <div v-if="error" class="feedback error">{{ error }}</div>
                    <div v-if="success" class="feedback success">{{ success }}</div>
                </div>

                <div class="selector-item selector-item--action">
                    <label v-if="!compact" class="form-label opacity-0 user-select-none" aria-hidden="true">Acción</label>
                    <button class="btn btn-primary btn-sm w-100 selector-action" type="button" @click="tryAdd">Agregar</button>
                </div>
            </div>

            <div v-if="suggestedFormations.length > 0" class="mt-3 selector-suggestions">
                <small class="text-muted d-block mb-2">Sugerencias rápidas</small>
                <div class="d-flex flex-wrap gap-2">
                    <button
                        v-for="suggestion in suggestedFormations"
                        :key="suggestion"
                        type="button"
                        class="btn btn-secondary btn-sm suggestion-chip"
                        @click="applySuggestion(suggestion)"
                    >
                        {{ suggestion }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { baseFormationsMap, footballModality, validateFormation, getSuggestedFormations } from './utils/formations.js'

const props = defineProps({
    formation: { type: String, required: true },
    modality: { type: Number, required: true },
    customFormations: { type: Object, default: () => ({}) },
    compact: { type: Boolean, default: false }
})

const emits = defineEmits(['change-formation', 'add-formation'])

const selectedFormation = ref(props.formation)
const modalitySelected = ref(props.modality)
const newFormation = ref('')
const error = ref('')
const success = ref('')
const uid = Math.random().toString(36).slice(2, 8)
const modalityId = `coachboard-modality-${uid}`
const systemId = `coachboard-system-${uid}`
const newSystemId = `coachboard-new-system-${uid}`

// Combinar formaciones base y personalizadas
const availableFormations = computed(() => {
    const base = baseFormationsMap[modalitySelected.value] || {}
    const custom = props.customFormations[modalitySelected.value] || {}

    return Object.keys({ ...base, ...custom })
})

// Sugerencias automáticas basadas en la modalidad
const suggestedFormations = computed(() => {
    return getSuggestedFormations(modalitySelected.value)
})

// Watchers
watch(() => props.formation, (newFormation) => {
    selectedFormation.value = newFormation
})

watch(() => props.modality, (newModality) => {
    modalitySelected.value = newModality
})

function emitChange() {
    emits('change-formation', {
        formation: selectedFormation.value,
        modality: modalitySelected.value
    })
}

function tryAdd() {
    error.value = ''
    success.value = ''

    const formationStr = newFormation.value.trim()

    if (!formationStr) {
        error.value = 'Ingresa una formación'
        return
    }

    const validation = validateFormation(formationStr, modalitySelected.value)
    if (!validation.valid) {
        error.value = validation.error
        return
    }

    // Verificar si ya existe
    const existingFormations = availableFormations.value
    if (existingFormations.includes(formationStr)) {
        error.value = 'Esta formación ya existe para esta modalidad'
        return
    }

    // Emitir la nueva formación
    emits('add-formation', {
        modality: modalitySelected.value,
        formation: formationStr,
        parts: validation.parts
    })

    // Seleccionar la nueva formación
    selectedFormation.value = formationStr
    success.value = `Formación ${formationStr} agregada correctamente`
    newFormation.value = ''

    // Emitir cambio después de agregar
    emitChange()

    setTimeout(() => {
        success.value = ''
    }, 3000)
}

function applySuggestion(formation) {
    selectedFormation.value = formation
    emitChange()
}
</script>

<style scoped>
.selector-head {
    min-width: 0;
}

.selector-title-compact {
    font-size: 0.95rem;
}

.selector-badge {
    font-size: 0.72rem;
    letter-spacing: 0.03em;
}

.selector-grid {
    display: grid;
    gap: 0.85rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.selector-grid--compact {
    gap: 0.65rem;
}

.selector-item--grow {
    grid-column: 1 / -1;
}

.selector-item--action {
    display: flex;
    flex-direction: column;
}

.selector-label {
    margin-bottom: 0.32rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #56675d;
}

.selector-action {
    min-height: 34px;
}

.suggestion-chip {
    line-height: 1.05;
}

.tactical-selector--compact .selector-grid {
    grid-template-columns: minmax(0, 1fr);
}

.tactical-selector--compact .selector-label {
    font-size: 0.73rem;
    margin-bottom: 0.28rem;
}

.tactical-selector--compact .form-select,
.tactical-selector--compact .form-control {
    min-height: 34px;
    font-size: 0.84rem;
}

.tactical-selector--compact .selector-action {
    min-height: 34px;
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
}

.tactical-selector--compact .selector-suggestions {
    margin-top: 0.7rem !important;
}

.tactical-selector--compact .suggestion-chip {
    padding: 0.24rem 0.5rem;
    font-size: 0.74rem;
}

.feedback {
    margin-top: 0.35rem;
    font-size: 0.82rem;
    font-weight: 600;
}

.feedback.error {
    color: #b93b3b;
}

.feedback.success {
    color: #237247;
}

@media (max-width: 767px) {
    .selector-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

:global(.dark) .selector-label {
    color: #b8d0c2;
}
</style>
