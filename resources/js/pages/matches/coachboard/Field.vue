<template>
    <section class="field-card">
        <header class="field-head">
            <div>
                <div class="section-kicker">Visualizador táctico</div>
                <h3 class="field-title">Cancha interactiva</h3>
                <p class="field-copy">
                    Arrastra jugadores desde la plantilla, reajusta posiciones en el campo y guarda la alineación como imagen.
                </p>
            </div>

            <div class="field-stats">
                <article class="stat-chip">
                    <span>Formación</span>
                    <strong>{{ formation }}</strong>
                </article>
                <article class="stat-chip">
                    <span>Titulares</span>
                    <strong>{{ assignedCount }}/{{ playerCount }}</strong>
                </article>
                <article class="stat-chip">
                    <span>Libres</span>
                    <strong>{{ openSlots }}</strong>
                </article>
            </div>
        </header>

        <div class="field-toolbar no-print">
            <div class="hint-pill">{{ canvasStatus }}</div>
            <div class="toolbar-actions">
                <button @click="resetToDefault" class="btn btn-secondary btn-sm" type="button">
                    Reiniciar
                </button>
                <button @click="saveCanvasImage" class="btn btn-success btn-sm" type="button">
                    PNG
                </button>
            </div>
        </div>

        <div class="field-wrapper">
            <div class="field-overlay">
                <div class="overlay-label">{{ formation }}</div>
                <div class="overlay-subtitle">Sistema activo</div>
            </div>

            <canvas
                ref="canvas"
                @dblclick="onDblClick"
                @dragover.prevent
                @drop.prevent="onDrop"
                @pointerdown="onCanvasPointerDown"
                @pointermove="onCanvasPointerMove"
                @pointerup="onCanvasPointerUp"
                @pointerleave="onCanvasPointerUp"
            ></canvas>
        </div>

        <div class="legend-row">
            <div class="legend-list">
                <span class="legend-item">
                    <i class="legend-dot defense"></i>
                    Defensa
                </span>
                <span class="legend-item">
                    <i class="legend-dot midfield"></i>
                    Medio campo
                </span>
                <span class="legend-item">
                    <i class="legend-dot attack"></i>
                    Ataque
                </span>
            </div>
        </div>
    </section>
</template>

<script setup>
import useCoachBoardField from '@/composables/matches/useCoachBoardField'

const props = defineProps({
    formation: { type: String, required: true },
    formationsMap: { type: Object, required: true },
    playersField: { type: Array, required: true },
    availablePlayers: { type: Array, default: () => [] },
    playerCount: { type: Number, default: 11 }, // Nuevo prop para número de jugadores
    includeGoalkeeper: { type: Boolean, default: true } // Si incluye portero o no
})

const emits = defineEmits(['assign-player', 'unassign-player', 'reset-lineup', 'update-positions'])

/**
 * Toda la lógica del canvas vive en el composable para que este componente
 * conserve solo responsabilidades de presentación y cableado con el template.
 */
const {
    canvas,
    canvasStatus,
    assignedCount,
    openSlots,
    applyFormation,
    getCanvasImage,
    onCanvasPointerDown,
    onCanvasPointerMove,
    onCanvasPointerUp,
    onDblClick,
    onDrop,
    resetToDefault,
    saveCanvasImage
} = useCoachBoardField(props, emits)

// Exponer funciones a padre
defineExpose({
    applyFormation,
    resetToDefault,
    saveCanvasImage,
    getCanvasImage
})
</script>

<style scoped>
.field-card {
    display: grid;
    gap: 1rem;
    padding: 1.2rem;
    border-radius: 26px;
    background:
        radial-gradient(circle at top left, rgba(50, 141, 88, 0.18), transparent 30%),
        linear-gradient(180deg, #fdfefe 0%, #f3f7f4 100%);
    border: 1px solid rgba(14, 58, 36, 0.12);
    box-shadow: 0 24px 54px rgba(17, 56, 36, 0.12);
}

.field-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.section-kicker {
    color: #4d7b63;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.76rem;
    font-weight: 800;
}

.field-title {
    margin: 0.25rem 0 0;
    color: #123524;
    font-size: 1.3rem;
    font-weight: 800;
}

.field-copy {
    margin: 0.5rem 0 0;
    max-width: 62ch;
    color: #607567;
    line-height: 1.5;
}

.field-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(88px, 1fr));
    gap: 0.75rem;
}

.stat-chip {
    padding: 0.8rem 0.9rem;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid rgba(16, 63, 40, 0.1);
    min-width: 96px;
}

.stat-chip span {
    display: block;
    color: #6b7e73;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.stat-chip strong {
    color: #173e2b;
    font-size: 1.15rem;
    font-weight: 800;
}

.field-toolbar {
    display: grid;
    gap: 0.9rem;
}

.hint-pill {
    display: flex;
    align-items: center;
    width: 100%;
    min-height: 40px;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    background: rgba(9, 29, 19, 0.88);
    color: #eef8f1;
    font-size: 0.9rem;
    line-height: 1.25;
}

.toolbar-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    width: 100%;
}

.field-wrapper {
    width: 100%;
    max-width: 860px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
    border-radius: 26px;
    box-shadow: 0 28px 52px rgba(8, 24, 16, 0.24);
    background: #102418;
}

.field-overlay {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 2;
    pointer-events: none;
}

.overlay-label {
    display: inline-flex;
    align-items: center;
    min-height: 38px;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    background: rgba(6, 19, 13, 0.78);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #fff;
    font-weight: 800;
    letter-spacing: 0.08em;
}

.overlay-subtitle {
    margin-top: 0.45rem;
    color: rgba(255, 255, 255, 0.84);
    font-size: 0.82rem;
    font-weight: 600;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
}

canvas {
    display: block;
    width: 100%;
    height: auto;
    touch-action: none;
    -webkit-tap-highlight-color: transparent;
    cursor: crosshair;
}

.legend-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.legend-list {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: #375242;
    font-weight: 700;
    font-size: 0.9rem;
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.legend-dot.defense {
    background: #58bff4;
}

.legend-dot.midfield {
    background: #e0b848;
}

.legend-dot.attack {
    background: #ef6770;
}

@media (max-width: 767px) {
    .field-stats {
        width: 100%;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .hint-pill {
        width: 100%;
    }
}

@media (max-width: 560px) {
    .field-card {
        padding: 1rem;
        border-radius: 22px;
    }

    .field-stats {
        grid-template-columns: 1fr;
    }

    .overlay-label {
        font-size: 0.85rem;
    }
}

:global(.dark) .field-card {
    background:
        radial-gradient(circle at top left, rgba(60, 150, 108, 0.18), transparent 30%),
        linear-gradient(180deg, #17221c 0%, #101813 100%);
    border-color: rgba(173, 214, 189, 0.12);
    box-shadow: 0 24px 54px rgba(0, 0, 0, 0.32);
}

:global(.dark) .section-kicker,
:global(.dark) .field-copy,
:global(.dark) .legend-item,
:global(.dark) .stat-chip span {
    color: #a7c3b3;
}

:global(.dark) .field-title,
:global(.dark) .stat-chip strong {
    color: #eef8f1;
}

:global(.dark) .stat-chip {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(194, 227, 209, 0.1);
}

:global(.dark) .hint-pill {
    background: rgba(235, 244, 239, 0.1);
    color: #edf7f1;
    border: 1px solid rgba(199, 226, 210, 0.12);
}

:global(.dark) .field-wrapper {
    background: #08110c;
    box-shadow: 0 28px 52px rgba(0, 0, 0, 0.42);
}

:global(.dark) .overlay-label {
    background: rgba(5, 13, 9, 0.8);
    border-color: rgba(255, 255, 255, 0.12);
}

:global(.dark) .overlay-subtitle {
    color: rgba(231, 243, 236, 0.82);
}
</style>
