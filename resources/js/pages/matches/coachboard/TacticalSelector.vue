<template>
    <section class="card border-0 shadow-sm no-print">
        <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <div class="small text-muted text-uppercase fw-semibold mb-1">Configuración táctica</div>
                <h3 class="h5 mb-0">Sistema de juego</h3>
            </div>
            <span class="badge rounded-pill text-bg-primary px-3 py-2">{{ selectedFormation }}</span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group">
                <label class="form-label" for="modality">Modalidad</label>
                <select class="form-select form-select-sm" v-model="modalitySelected" @change="emitChange" id="modality">
                    <option v-for="(label, key) in footballModality" :key="key" :value="parseInt(key)">
                        {{ label }}
                    </option>
                </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                <label class="form-label" for="systemt">Sistema táctico</label>
                <select class="form-select form-select-sm" v-model="selectedFormation" @change="emitChange" id="systemt">
                    <option v-for="formation in availableFormations" :key="formation" :value="formation">
                        {{ formation }}
                    </option>
                </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                <label class="form-label" for="newsystem">Crear nuevo sistema</label>
                <input
                    v-model="newFormation"
                    placeholder="Ej: 4-3-3"
                    class="form-control form-control-sm"
                    id="newsystem"
                    @keyup.enter="tryAdd"
                />
                <div v-if="error" class="feedback error">{{ error }}</div>
                <div v-if="success" class="feedback success">{{ success }}</div>
                </div>
            </div>

            <div class="col-md-6 d-flex flex-column">
                <label class="form-label opacity-0 user-select-none" aria-hidden="true">Acción</label>
                <button class="btn btn-primary btn-sm w-100 py-2" type="button" @click="tryAdd">Agregar</button>
            </div>
        </div>

        <div v-if="suggestedFormations.length > 0" class="mt-3">
            <small class="text-muted d-block mb-2">Sugerencias rápidas</small>
            <div class="d-flex flex-wrap gap-2">
                <button
                    v-for="suggestion in suggestedFormations"
                    :key="suggestion"
                    type="button"
                    class="btn btn-secondary btn-sm"
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
    customFormations: { type: Object, default: () => ({}) }
})

const emits = defineEmits(['change-formation', 'add-formation'])

const selectedFormation = ref(props.formation)
const modalitySelected = ref(props.modality)
const newFormation = ref('')
const error = ref('')
const success = ref('')

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
</style>
