<template>

    <div class="row">
        <div class="col-xl-6 col-lg-6 col-sm-12">

            <Field ref="field" :formation="currentFormation" :formations-map="currentFormationsMap" :players-field="playersField"
                :player-count="currentModality" :include-goalkeeper="true" @assign-player="handleAssignPlayer"
                @unassign-player="handleUnassignPlayer" @update-positions="handleUpdatePositions" @layout-saved="onLayoutSaved"/>

        </div>
        <div class="col-xl-6 col-lg-6 col-sm-12">
            <TacticalSelector :formation="currentFormation" :modality="currentModality"
                :custom-formations="customFormations" @change-formation="handleFormationChange"
                @add-formation="handleAddFormation" />

            <div class="mt-3">
                <PlayerList :players="availablePlayers" @dragstart="onPlayerDragStart" />
            </div>


        </div>
    </div>

</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import TacticalSelector from './TacticalSelector.vue'
import PlayerList from './PlayerList.vue'
import Field from './Field.vue'
import { baseFormationsMap } from './utils/formations.js'

const props = defineProps({
    initialPlayers: {
        type: Array,
        default: [],
    }
})

const currentFormation = ref('4-4-2')
const currentModality = ref(11)
const playersField = ref([])
const customFormations = ref({})
const availablePlayers = ref([])
// Formaciones actuales para Field.vue (solo de la modalidad actual)
const currentFormationsMap = computed(() => {
    const base = baseFormationsMap[currentModality.value] || {}
    const custom = customFormations.value[currentModality.value] || {}

    return {
        ...base,
        ...custom
    }
})

// Cargar formaciones personalizadas desde localStorage
onMounted(() => {
    const saved = localStorage.getItem('custom-formations')
    if (saved) {
        try {
            customFormations.value = JSON.parse(saved)
        } catch (e) {
            console.error('Error loading custom formations:', e)
        }
    }
})

watch(() => props.initialPlayers, (newValue, oldValue) => {
    availablePlayers.value = newValue.map(p => ({ id: p.id, name: `${p.last_names} ${p.names}`, img: p.photo_url }))
}, { deep: true })


// Guardar cuando cambien las formaciones personalizadas
watch(customFormations, (newFormations) => {
    localStorage.setItem('custom-formations', JSON.stringify(newFormations))
}, { deep: true })

function handleFormationChange({ formation, modality }) {
    currentFormation.value = formation
    currentModality.value = modality
}

function handleAddFormation({ modality, formation, parts }) {
    if (!customFormations.value[modality]) {
        customFormations.value[modality] = {}
    }
    customFormations.value[modality][formation] = parts

    // Actualizar formación actual si es la misma modalidad
    if (modality === currentModality.value) {
        currentFormation.value = formation
    }
}

/* ---------- Handlers ---------- */
const field = ref(null)

// cuando arranca el Field, le pasamos players vía prop (ya lo hace)
function onPlayerDragStart(player) {
    // nada adicional por ahora
}

function handleAssignPlayer({ player, posKey }) {
    // player fue asignado en Field; quitarlo de available o bench
    availablePlayers.value = availablePlayers.value.filter(p => p.id !== player.id)
    if (!playersField.value.find(p => p.id === player.id)) {
        playersField.value.push(player)
    }
}

function handleUnassignPlayer({ player }) {
    // devolver a available (si no existe)
    if (!availablePlayers.value.find(p => p.id === player.id)) {
        availablePlayers.value.push(player)
    }
    playersField.value = playersField.value.filter(p => p.id !== player.id)
}

function handleUpdatePositions() { }


function onLayoutSaved(obj) {
    const data = JSON.stringify(obj, null, 2)
    console.log(data)
    console.log('layout guardado', obj)
}
function onLayoutLoaded(obj) {
    // si hay assigned con id: quitar esos players de available y bench
    if (!obj || !obj.positions) return
    for (const k in obj.positions) {
        const a = obj.positions[k].assigned
        if (a) {
            // quitarlo
            availablePlayers.value = availablePlayers.value.filter(p => p.id !== a.id)
        }
    }
}

function resetAll() {
    field.value.resetToDefault()
}

</script>