<template>
    <div class="mb-3 no-print">

        <div class="row col-md-12">
            <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                <div class="form-group">
                    <label class="form-label" for="modality">Modalidad</label>
                    <select class="form-select form-select-sm" v-model="modalitySelected" @change="emitChange" id="modality">
                        <option v-for="(label, key) in footballModality" :key="key" :value="parseInt(key)">{{ label }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                <div class="form-group">
                    <label class="form-label" for="systemt">Sistema táctico</label>
                    <select class="form-select form-select-sm" v-model="selectedFormation" @change="emitChange" id="systemt">
                        <option v-for="formation in availableFormations" :key="formation" :value="formation">{{
                            formation }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-lg-3 col-xl-3">
                <div class="form-group">
                    <label class="form-label" for="newsystem">Agregar sistema táctico</label>
                    <input v-model="newFormation" placeholder="ej: 4-3-3" class="form-control form-control-sm" id="newsystem" />
                    <div v-if="error" class="custom-error">{{ error }}</div>
                    <div v-if="success" class="text-success small mt-1">{{ success }}</div>
                </div>

            </div>
            <div class="col-md-6 col-sm-6 col-lg-3 col-xl-3">
                <!-- <div class="form-group"> -->
                    <div class="mt-4">
                        <button class="btn btn-sm btn-primary" @click="tryAdd">Agregar</button>
                    </div>
                <!-- </div> -->

            </div>
        </div>









        <!-- Sugerencias automáticas -->
        <div v-if="suggestedFormations.length > 0" class="mt-2">
            <small class="text-muted">Sugerencias: </small>
            <button v-for="suggestion in suggestedFormations" :key="suggestion"
                class="btn btn-sm btn-outline-secondary ms-1 mb-1" @click="applySuggestion(suggestion)">
                {{ suggestion }}
            </button>
        </div>
    </div>
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