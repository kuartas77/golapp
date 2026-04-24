<template>
    <section class="field-card">
        <header class="field-head">
            <div>
                <div class="section-kicker">Visualizador táctico</div>
                <h3 class="field-title">Cancha interactiva</h3>
                <small class="field-copy">
                    Arrastra jugadores desde la plantilla, reajusta posiciones en el campo y guarda la alineación como imagen.
                </small>
            </div>

            <!-- <div class="field-stats">
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
            </div> -->
        </header>

        <div class="field-toolbar no-print">
            <!-- <div class="hint-pill">{{ canvasStatus }}</div> -->
            <div class="toolbar-actions">
                <button @click="resetToDefault" class="btn btn-secondary btn-sm" type="button">
                    Reiniciar
                </button>
                <button @click="saveCanvasImage" class="btn btn-success btn-sm" type="button">
                    PNG
                </button>
            </div>
        </div>

        <div class="field-stage">
            <aside class="field-config no-print">
                <TacticalSelector
                    compact
                    :formation="formation"
                    :modality="modality"
                    :custom-formations="customFormations"
                    @change-formation="handleFormationChange"
                    @add-formation="handleAddFormation"
                />
            </aside>

            <div class="field-wrapper">
                <div class="field-overlay">
                    <div class="overlay-label">{{ formation }}</div>
                    <div class="overlay-subtitle">Sistema activo</div>
                </div>

                <svg
                    ref="canvas"
                    class="field-svg"
                    :viewBox="fieldViewBox"
                    preserveAspectRatio="xMidYMid meet"
                    role="img"
                    :aria-label="`Cancha táctica con formación ${formation}`"
                    @dblclick="onDblClick"
                    @dragover.prevent
                    @drop.prevent="onDrop"
                    @pointerdown="onCanvasPointerDown"
                    @pointermove="onCanvasPointerMove"
                    @pointerup="onCanvasPointerUp"
                    @pointerleave="onCanvasPointerLeave"
                    @click="onCanvasClick"
                >
                    <defs>
                        <linearGradient :id="svgIds.topShade" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#000000" stop-opacity="0.22" />
                            <stop offset="100%" stop-color="#000000" stop-opacity="0" />
                        </linearGradient>

                        <linearGradient :id="svgIds.bottomShade" x1="0" y1="1" x2="0" y2="0">
                            <stop offset="0%" stop-color="#000000" stop-opacity="0.26" />
                            <stop offset="100%" stop-color="#000000" stop-opacity="0" />
                        </linearGradient>

                        <linearGradient :id="svgIds.badgeFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0c1c16" stop-opacity="0.9" />
                            <stop offset="100%" stop-color="#07120e" stop-opacity="0.94" />
                        </linearGradient>

                        <filter :id="svgIds.badgeShadow" x="-20%" y="-20%" width="140%" height="160%">
                            <feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#000000" flood-opacity="0.28" />
                        </filter>

                        <clipPath v-for="position in renderedPositions" :id="position.clipId" :key="position.clipId">
                            <circle :cx="position.x" :cy="position.y" r="31" />
                        </clipPath>
                    </defs>

                    <image
                        :href="fieldImageSrc"
                        x="0"
                        y="0"
                        :width="canvasWidth"
                        :height="canvasHeight"
                        preserveAspectRatio="none"
                    />

                    <rect
                        x="0"
                        y="0"
                        :width="canvasWidth"
                        :height="canvasHeight * 0.25"
                        :fill="`url(#${svgIds.topShade})`"
                    />

                    <rect
                        x="0"
                        :y="canvasHeight * 0.76"
                        :width="canvasWidth"
                        :height="canvasHeight * 0.24"
                        :fill="`url(#${svgIds.bottomShade})`"
                    />

                    <g
                        v-for="position in renderedPositions"
                        :key="position.key"
                        class="field-slot"
                        :class="{
                            'is-hovered': position.isHovered,
                            'is-assigned': Boolean(position.assigned),
                            'is-draggable': Boolean(position.assigned) && position.key !== 'GK'
                        }"
                    >
                        <circle
                            class="slot-glow"
                            :cx="position.x"
                            :cy="position.y"
                            :r="position.isHovered ? 48 : 44"
                            :fill="position.palette.glow"
                        />

                        <circle
                            class="slot-surface"
                            :cx="position.x"
                            :cy="position.y"
                            :r="position.isHovered ? 40 : 36"
                            :fill="position.palette.fill"
                            :stroke="position.palette.stroke"
                            :stroke-width="position.isHovered ? 3 : 2"
                        />

                        <circle
                            class="slot-highlight"
                            :cx="position.x - 8"
                            :cy="position.y - 10"
                            :r="position.isHovered ? 12 : 10"
                        />

                        <circle
                            v-if="position.isHovered"
                            class="slot-hover-ring"
                            :cx="position.x"
                            :cy="position.y"
                            :r="47"
                        />

                        <template v-if="position.assigned">
                            <image
                                v-if="position.assigned.img"
                                class="slot-avatar"
                                :href="position.assigned.img"
                                :x="position.x - 31"
                                :y="position.y - 31"
                                width="62"
                                height="62"
                                preserveAspectRatio="xMidYMid slice"
                                :clip-path="`url(#${position.clipId})`"
                            />

                            <text
                                v-else
                                class="slot-text"
                                :x="position.x"
                                :y="position.y"
                            >
                                {{ position.fallbackLabel }}
                            </text>

                            <line
                                class="slot-connector"
                                :x1="position.x"
                                :y1="position.y + 36"
                                :x2="position.x"
                                :y2="position.badgeY"
                            />

                            <g
                                class="slot-badge"
                                :transform="`translate(${position.badgeX} ${position.badgeY})`"
                                :filter="`url(#${svgIds.badgeShadow})`"
                            >
                                <rect
                                    class="slot-badge-fill"
                                    :width="position.badgeWidth"
                                    :height="position.badgeHeight"
                                    rx="15"
                                    ry="15"
                                    :fill="`url(#${svgIds.badgeFill})`"
                                />
                                <rect
                                    class="slot-badge-stroke"
                                    :width="position.badgeWidth"
                                    :height="position.badgeHeight"
                                    rx="15"
                                    ry="15"
                                />
                            </g>

                            <text
                                class="slot-name"
                                :x="position.x"
                                :y="position.roleText ? position.badgeY + 13 : position.badgeY + position.badgeHeight / 2"
                            >
                                {{ position.playerShortName }}
                            </text>

                            <text
                                v-if="position.roleText"
                                class="slot-role"
                                :x="position.x"
                                :y="position.badgeY + 29"
                            >
                                {{ position.roleText }}
                            </text>
                        </template>

                        <text
                            v-else
                            class="slot-text"
                            :x="position.x"
                            :y="position.y"
                        >
                            {{ position.fallbackLabel }}
                        </text>
                    </g>
                </svg>
            </div>
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
import TacticalSelector from './TacticalSelector.vue'
import useCoachBoardField from '@/composables/matches/useCoachBoardField'

const props = defineProps({
    formation: { type: String, required: true },
    formationsMap: { type: Object, required: true },
    modality: { type: Number, required: true },
    customFormations: { type: Object, default: () => ({}) },
    playersField: { type: Array, required: true },
    availablePlayers: { type: Array, default: () => [] },
    selectedPlayer: { type: Object, default: null },
    playerCount: { type: Number, default: 11 }, // Nuevo prop para número de jugadores
    includeGoalkeeper: { type: Boolean, default: true } // Si incluye portero o no
})

const emits = defineEmits([
    'assign-player',
    'unassign-player',
    'change-formation',
    'add-formation',
    'clear-selected-player',
    'reset-lineup',
    'update-positions'
])

/**
 * Toda la lógica del SVG vive en el composable para que este componente
 * conserve solo responsabilidades de presentación y cableado con el template.
 */
const {
    canvas,
    canvasWidth,
    canvasHeight,
    canvasStatus,
    assignedCount,
    openSlots,
    clipPathPrefix,
    fieldViewBox,
    fieldImageSrc,
    renderedPositions,
    applyFormation,
    getCanvasImage,
    onCanvasClick,
    onCanvasPointerDown,
    onCanvasPointerMove,
    onCanvasPointerUp,
    onCanvasPointerLeave,
    onDblClick,
    onDrop,
    resetToDefault,
    saveCanvasImage
} = useCoachBoardField(props, emits)

const svgIds = {
    topShade: `${clipPathPrefix}-top-shade`,
    bottomShade: `${clipPathPrefix}-bottom-shade`,
    badgeFill: `${clipPathPrefix}-badge-fill`,
    badgeShadow: `${clipPathPrefix}-badge-shadow`
}

function handleFormationChange(payload) {
    emits('change-formation', payload)
}

function handleAddFormation(payload) {
    emits('add-formation', payload)
}

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
    font-size: 1rem;
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

.field-stage {
    display: grid;
    gap: 1rem;
    align-items: start;
}

.field-config {
    min-width: 0;
}

.field-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
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

.field-svg {
    display: block;
    width: 100%;
    height: auto;
    touch-action: none;
    -webkit-tap-highlight-color: transparent;
    cursor: crosshair;
}

.field-slot {
    transform-box: fill-box;
    transform-origin: center;
}

.field-slot.is-draggable {
    cursor: grab;
}

.field-slot.is-hovered {
    cursor: grab;
}

.slot-glow {
    opacity: 0.9;
}

.slot-surface {
    filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.24));
}

.slot-highlight {
    fill: rgba(255, 255, 255, 0.72);
    pointer-events: none;
}

.slot-hover-ring {
    fill: none;
    stroke: rgba(255, 255, 255, 0.75);
    stroke-width: 2;
    pointer-events: none;
}

.slot-avatar,
.slot-text,
.slot-connector,
.slot-badge,
.slot-name,
.slot-role {
    pointer-events: none;
}

.slot-text,
.slot-name,
.slot-role {
    text-anchor: middle;
    dominant-baseline: middle;
}

.slot-text {
    fill: #0b1d17;
    font-size: 13px;
    font-weight: 700;
}

.slot-connector {
    stroke: rgba(255, 255, 255, 0.8);
    stroke-width: 1.5;
}

.slot-badge-stroke {
    fill: none;
    stroke: rgba(255, 255, 255, 0.7);
    stroke-width: 1.6;
}

.slot-name {
    fill: #ffffff;
    font-size: 13px;
    font-weight: 700;
}

.slot-role {
    fill: rgba(194, 231, 212, 0.9);
    font-size: 10px;
    font-weight: 600;
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

@media (min-width: 992px) {
    .field-stage {
        grid-template-columns: minmax(220px, 250px) minmax(0, 1fr);
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
