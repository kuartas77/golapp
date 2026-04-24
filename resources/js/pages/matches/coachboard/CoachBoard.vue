<template>
    <section class="coachboard-page">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div >
                <div class="small text-muted text-uppercase fw-semibold mb-2">Pizarra táctica</div>
                <h2 class="h4 mb-2">Construcción de alineación inicial</h2>
                <!-- <p class="text-muted mb-0 coachboard-copy">
                    Ajusta el sistema, arrastra jugadores al campo y reorganiza posiciones para preparar la formación.
                </p> -->
            </div>

            <div class="row g-2 coachboard-metrics">
                <div class="col-sm-4 col-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body ">
                            <div class="small text-muted text-uppercase fw-semibold ">Formación</div>
                            <div class="fs-6 fw-bold">{{ currentFormation }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body ">
                            <div class="small text-muted text-uppercase fw-semibold ">Titulares</div>
                            <div class="fs-6 fw-bold">{{ startersCount }}/{{ currentModality }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body ">
                            <div class="small text-muted text-uppercase fw-semibold ">Disponibles</div>
                            <div class="fs-6 fw-bold">{{ availablePlayers.length }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 align-items-start">
            <div ref="fieldSection" class="col-xl-12 col-lg-12 col-12">
                <Field
                    :formation="currentFormation"
                    :formations-map="currentFormationsMap"
                    :modality="currentModality"
                    :custom-formations="customFormations"
                    :players-field="playersField"
                    :available-players="availablePlayers"
                    :player-count="currentModality"
                    :selected-player="selectedAvailablePlayer"
                    :include-goalkeeper="true"
                    @assign-player="handleAssignPlayer"
                    @unassign-player="handleUnassignPlayer"
                    @change-formation="handleFormationChange"
                    @add-formation="handleAddFormation"
                    @clear-selected-player="clearSelectedPlayer"
                    @reset-lineup="handleResetLineup"
                    @update-positions="handleUpdatePositions"
                />
            </div>

            <div class="col-xl-12 col-lg-12 col-12">
                <div class="row g-3">
                    <div class="col-12">
                        <PlayerList
                            :players="availablePlayers"
                            :selected-player-id="selectedAvailablePlayerId"
                            @dragstart="handlePlayerDragStart"
                            @select-player="handleSelectPlayer"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
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
const selectedAvailablePlayerId = ref(null)
const latestPositionsSnapshot = ref({ positions: {} })
const hasLineupInteraction = ref(false)
const fieldSection = ref(null)
const startersCount = computed(() => playersField.value.length)
const allPlayers = computed(() => props.initialPlayers.map(normalizePlayer))
const selectedAvailablePlayer = computed(() =>
    availablePlayers.value.find(player => player.id === selectedAvailablePlayerId.value) || null
)
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

watch(() => props.initialPlayers, () => {
    const assignedIds = new Set(playersField.value.map(player => player.id))
    availablePlayers.value = allPlayers.value.filter(player => !assignedIds.has(player.id))
}, { deep: true, immediate: true })

watch(selectedAvailablePlayer, (player) => {
    if (!player && selectedAvailablePlayerId.value !== null) {
        selectedAvailablePlayerId.value = null
    }
})


// Guardar cuando cambien las formaciones personalizadas
watch(customFormations, (newFormations) => {
    localStorage.setItem('custom-formations', JSON.stringify(newFormations))
}, { deep: true })

function handleFormationChange({ formation, modality }) {
    currentFormation.value = formation
    currentModality.value = modality
    hasLineupInteraction.value = true
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

    hasLineupInteraction.value = true
}

/* ---------- Handlers ---------- */
function normalizePlayer(player) {
    const playerData = player.player || player

    return {
        ...player,
        player: playerData,
        id: playerData.id ?? player.id ?? player.inscription_id,
        inscription_id: player.inscription_id ?? null,
        unique_code: playerData.unique_code ?? player.unique_code ?? null,
        name: playerData.full_names || `${playerData.last_names || ''} ${playerData.names || ''}`.trim(),
        img: playerData.photo_url_public || playerData.photo_url || playerData.photo_local || player.photo_url || null
    }
}

function handleAssignPlayer({ player }) {
    hasLineupInteraction.value = true
    availablePlayers.value = availablePlayers.value.filter(p => p.id !== player.id)
    if (!playersField.value.find(p => p.id === player.id)) {
        playersField.value.push(player)
    }

    if (selectedAvailablePlayerId.value === player.id) {
        clearSelectedPlayer()
    }
}

function handleUnassignPlayer({ player }) {
    hasLineupInteraction.value = true
    const originalPlayer = allPlayers.value.find(p => p.id === player.id) || player
    if (!availablePlayers.value.find(p => p.id === player.id)) {
        availablePlayers.value.push(originalPlayer)
    }
    playersField.value = playersField.value.filter(p => p.id !== player.id)
}

function handleResetLineup() {
    hasLineupInteraction.value = true
    playersField.value = []
    availablePlayers.value = [...allPlayers.value]
    clearSelectedPlayer()
}

function clearSelectedPlayer() {
    selectedAvailablePlayerId.value = null
}

function focusFieldForMobile() {
    if (typeof window === 'undefined' || window.innerWidth >= 992) return

    const fieldCanvasWrapper = fieldSection.value?.querySelector('.selector-item--grow')

    ;(fieldCanvasWrapper || fieldSection.value)?.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    })
}

function handleSelectPlayer(player) {
    const nextId = selectedAvailablePlayerId.value === player.id ? null : player.id
    selectedAvailablePlayerId.value = nextId

    if (nextId) {
        focusFieldForMobile()
    }
}

function handlePlayerDragStart() {
    clearSelectedPlayer()
}

function formatLineupPosition(position) {
    if (!position) return ''

    return position
        .replace(/\(/g, ' (')
        .replace(/\s+/g, ' ')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
}

function getSkillControlsPayload() {
    const positions = latestPositionsSnapshot.value?.positions || {}
    const assignedByPlayerId = new Map()

    Object.values(positions).forEach((position) => {
        if (!position?.assigned?.id) return

        assignedByPlayerId.set(position.assigned.id, {
            inscription_id: position.assigned.inscription_id ?? null,
            titular: 1,
            position: formatLineupPosition(position.specificRole || ''),
            player: {
                id: position.assigned.id,
                full_names: position.assigned.player?.full_names || position.assigned.name || ''
            }
        })
    })

    return allPlayers.value.map((player) => {
        const assignedPlayer = assignedByPlayerId.get(player.id)

        return {
            inscription_id: assignedPlayer?.inscription_id ?? player.inscription_id ?? null,
            titular: assignedPlayer ? 1 : 0,
            position: assignedPlayer?.position || '',
            player: {
                id: player.id,
                full_names: player.player?.full_names || player.name || ''
            }
        }
    })
}

function handleUpdatePositions(snapshot) {
    latestPositionsSnapshot.value = snapshot

    if (playersField.value.length > 0) {
        hasLineupInteraction.value = true
    }
}

defineExpose({
    getSkillControlsPayload,
    hasLineupInteraction: () => hasLineupInteraction.value
})

</script>

<style scoped>
.coachboard-page {
    display: grid;
    gap: 1.25rem;
}

.coachboard-copy {
    max-width: 62ch;
}

.coachboard-metrics {
    min-width: min(100%, 420px);
}

@media (max-width: 767px) {
    .coachboard-metrics {
        min-width: 100%;
    }
}
</style>
