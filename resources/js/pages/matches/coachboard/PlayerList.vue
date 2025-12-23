<template>
    <div class="mb-3">
        <label class="form-label">Jugadores disponibles / Suplentes</label>

        <div class="d-flex gap-2 flex-wrap player-list">
            <div v-for="p in players" :key="p.id" class="text-center" draggable="true"
                @dragstart="onDragStart($event, p)" style="width:80px;">
                <img :src="p.img" alt="suple" width="60" height="60" class="rounded-circle" />
                <div class="small">{{ p.name }}</div>
            </div>
            <div v-if="players.length === 0" class="text-muted small">No quedan jugadores</div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    players: { type: Array, required: true }
})
const emits = defineEmits(['dragstart'])

function onDragStart(e, player) {
    // colocamos el objeto completo en dataTransfer como JSON
    e.dataTransfer.setData('application/json', JSON.stringify(player))
    emits('dragstart', player)
}
</script>

<style scoped>
img {
    object-fit: cover;
}
.player-list{
    cursor: move; /* fallback if grab cursor is unsupported */
    cursor: grab;
    cursor: -moz-grab;
    cursor: -webkit-grab;
}
.player-list:active {
    cursor: grabbing;
    cursor: -moz-grabbing;
    cursor: -webkit-grabbing;
}
</style>