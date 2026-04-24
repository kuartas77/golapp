<template>
    <section class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-2">
                <div>
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Plantilla disponible</div>
                    <h3 class="h5 mb-0">Jugadores listos para arrastrar</h3>
                </div>
                <span class="badge rounded-pill text-muted px-3 py-2">{{ players.length }}</span>
            </div>

            <p class="text-muted small mb-3">
                Arrastra cada jugador al campo para asignarlo a una posición. Doble clic sobre un titular para regresarlo.
            </p>

            <div class="row g-1 player-list">
                <div v-for="p in players" :key="p.id" class="col-xl-3 col-md-3 col-12">
                    <div
                        class="player-card h-100 border rounded-3 p-2 d-flex align-items-center gap-1"
                        draggable="true"
                        @dragstart="onDragStart($event, p)"
                    >
                        <img :src="p.img" :alt="p.name" width="56" height="56" class="player-avatar" />
                        <div class="min-w-0">
                            <div class="small fw-semibold text-break">{{ p.name }}</div>
                            <small class="text-muted player-hint">Arrastrar al campo</small>
                        </div>
                    </div>
                </div>
                <div v-if="players.length === 0" class="col-12">
                    <div class="empty-state border rounded-3 p-3 text-center text-muted small">
                        No quedan jugadores disponibles. El once ya está completo.
                    </div>
                </div>
            </div>
        </div>
    </section>
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
.player-list {
    cursor: grab;
    cursor: -moz-grab;
    cursor: -webkit-grab;
}

.player-card {
    transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
}

.player-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 28px rgba(17, 40, 63, 0.1);
}

.player-avatar {
    object-fit: cover;
    border-radius: 50%;
    flex-shrink: 0;
}

.player-hint {
    font-size: 0.6rem;
}

.player-list:active {
    cursor: grabbing;
    cursor: -moz-grabbing;
    cursor: -webkit-grabbing;
}

:global(.dark) .player-card {
    background: rgba(255, 255, 255, 0.04);
}

:global(.dark) .player-card:hover {
    box-shadow: 0 16px 28px rgba(0, 0, 0, 0.22);
}

:global(.dark) .empty-state {
    background: rgba(255, 255, 255, 0.04);
}
</style>
