<template>
    <div class="field-editor">
        <div class="field-toolbar" role="toolbar" aria-label="Herramientas de cancha">
            <button
                v-for="tool in tools"
                :key="tool.type"
                type="button"
                class="btn btn-primary btn-sm"
                @click="addItem(tool.type)"
            >
                <i :class="tool.icon" aria-hidden="true"></i>
                <span>{{ tool.label }}</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm" :disabled="!selectedId" @click="removeSelected">
                <i class="fa fa-trash fa-width-auto" aria-hidden="true"></i>
                <span>Eliminar</span>
            </button>
        </div>

        <label v-if="selectedItem?.type === 'text'" class="field-text-input">
            Texto
            <input :value="selectedItem.label" type="text" class="form-control form-control-sm" @input="updateSelectedLabel">
        </label>

        <div v-if="selectedItem?.type === 'arrow'" class="field-arrow-controls" aria-label="Orientación de flecha">
            <span>Orientación</span>
            <button type="button" class="btn btn-secondary btn-sm" @click="rotateSelectedArrow(-45)">
                <i class="fa fa-rotate-left fa-width-auto" aria-hidden="true"></i>
                <span>Izquierda</span>
            </button>
            <button type="button" class="btn btn-secondary btn-sm" @click="rotateSelectedArrow(45)">
                <i class="fa fa-rotate-right fa-width-auto" aria-hidden="true"></i>
                <span>Derecha</span>
            </button>
        </div>

        <svg
            ref="svgRef"
            class="soccer-field"
            viewBox="0 0 100 64"
            role="img"
            aria-label="Cancha editable"
            @pointermove="moveSelected"
            @pointerup="stopDrag"
            @pointerleave="stopDrag"
        >
            <rect x="1" y="1" width="98" height="62" rx="1.5" class="field-border" />
            <line x1="50" y1="1" x2="50" y2="63" class="field-line" />
            <circle cx="50" cy="32" r="9" class="field-line fill-none" />
            <circle cx="50" cy="32" r="1" class="field-dot" />
            <rect x="1" y="18" width="16" height="28" class="field-line fill-none" />
            <rect x="83" y="18" width="16" height="28" class="field-line fill-none" />
            <rect x="1" y="24" width="6" height="16" class="field-line fill-none" />
            <rect x="93" y="24" width="6" height="16" class="field-line fill-none" />
            <circle cx="11" cy="32" r="1" class="field-dot" />
            <circle cx="89" cy="32" r="1" class="field-dot" />

            <g
                v-for="item in items"
                :key="item.id"
                class="field-item"
                :class="{ selected: item.id === selectedId }"
                tabindex="0"
                @pointerdown.stop="startDrag(item, $event)"
                @click.stop="selectedId = item.id"
            >
                <circle v-if="item.type === 'player'" :cx="item.x" :cy="item.y" r="2.8" class="player" />
                <path v-else-if="item.type === 'cone'" :d="conePath(item)" class="cone" />
                <circle v-else-if="item.type === 'ball'" :cx="item.x" :cy="item.y" r="2.2" class="ball" />
                <g v-else-if="item.type === 'arrow'" :transform="arrowTransform(item)">
                    <line :x1="item.x - 4" :y1="item.y + 2.4" :x2="item.x + 3.15" :y2="item.y - 1.9" class="arrow-line" />
                    <path :d="arrowHeadPath(item)" class="arrow-head" />
                </g>
                <g v-else-if="item.type === 'xmark'">
                    <line :x1="item.x - 1.2" :y1="item.y - 1.2" :x2="item.x + 1.2" :y2="item.y + 1.2" class="xmark-line" />
                    <line :x1="item.x + 1.2" :y1="item.y - 1.2" :x2="item.x - 1.2" :y2="item.y + 1.2" class="xmark-line" />
                </g>
                <text v-else :x="item.x" :y="item.y" class="field-label">{{ item.label || 'Texto' }}</text>
            </g>
        </svg>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['update:modelValue'])

const svgRef = ref(null)
const selectedId = ref(null)
const dragState = ref(null)

const tools = [
    { type: 'player', label: 'Jugador', icon: 'fa fa-user fa-width-auto' },
    { type: 'cone', label: 'Cono', icon: 'fa fa-warning fa-width-auto' },
    { type: 'ball', label: 'Balón', icon: 'fa fa-circle fa-width-auto' },
    { type: 'arrow', label: 'Flecha', icon: 'fa fa-arrow-right fa-width-auto' },
    { type: 'xmark', label: 'X', icon: 'fa fa-xmark fa-width-auto' },
    { type: 'text', label: 'Texto', icon: 'fa fa-font fa-width-auto' },
]

const items = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
})

const selectedItem = computed(() => items.value.find((item) => item.id === selectedId.value))

function makeId() {
    return `${Date.now()}-${Math.random().toString(16).slice(2)}`
}

function addItem(type) {
    const item = {
        id: makeId(),
        type,
        x: 50,
        y: 32,
        label: type === 'text' ? 'Texto' : '',
        ...(type === 'arrow' ? { rotation: 0 } : {}),
    }

    items.value = [...items.value, item]
    selectedId.value = item.id
}

function removeSelected() {
    items.value = items.value.filter((item) => item.id !== selectedId.value)
    selectedId.value = null
}

function updateSelectedLabel(event) {
    items.value = items.value.map((item) => item.id === selectedId.value
        ? { ...item, label: event.target.value }
        : item
    )
}

function rotateSelectedArrow(delta) {
    items.value = items.value.map((item) => {
        if (item.id !== selectedId.value || item.type !== 'arrow') {
            return item
        }

        return {
            ...item,
            rotation: normalizeRotation(Number(item.rotation ?? 0) + delta),
        }
    })
}

function startDrag(item, event) {
    selectedId.value = item.id
    dragState.value = {
        id: item.id,
        pointerId: event.pointerId,
    }
    event.currentTarget.setPointerCapture?.(event.pointerId)
}

function moveSelected(event) {
    if (!dragState.value || !svgRef.value) {
        return
    }

    const point = svgRef.value.createSVGPoint()
    point.x = event.clientX
    point.y = event.clientY
    const svgPoint = point.matrixTransform(svgRef.value.getScreenCTM().inverse())
    const x = Math.min(97, Math.max(3, Number(svgPoint.x.toFixed(2))))
    const y = Math.min(61, Math.max(3, Number(svgPoint.y.toFixed(2))))

    items.value = items.value.map((item) => item.id === dragState.value.id ? { ...item, x, y } : item)
}

function stopDrag() {
    dragState.value = null
}

function conePath(item) {
    return `M ${item.x} ${item.y - 3} L ${item.x - 3} ${item.y + 3} L ${item.x + 3} ${item.y + 3} Z`
}

function arrowHeadPath(item) {
    return `M ${item.x + 4.6} ${item.y - 2.75} L ${item.x + 1.85} ${item.y - 2.95} L ${item.x + 3.15} ${item.y - 0.55} Z`
}

function arrowTransform(item) {
    return `rotate(${normalizeRotation(Number(item.rotation ?? 0))} ${item.x} ${item.y})`
}

function normalizeRotation(rotation) {
    return ((rotation % 360) + 360) % 360
}
</script>

<style scoped lang="scss">
@use '@/assets/base/color_variables';

.field-editor {
    --field-editor-border: #{color_variables.$m-color_3};
    --field-editor-label: #{color_variables.$dark};
    --field-editor-surface: #{color_variables.$white};
    --field-editor-input-bg: #{color_variables.$white};
    --field-grass: #{color_variables.$l-success};
    --field-grass-fill: #{color_variables.$l-success};
    --field-line-color: #{color_variables.$m-color_14};
    --field-player-color: #{color_variables.$info};
    --field-cone-color: #{color_variables.$warning};
    --field-ball-color: #{color_variables.$m-color_23};
    --field-arrow-color: #{color_variables.$danger};
    --field-xmark-color: #{color_variables.$m-color_23};
    --field-label-color: #{color_variables.$m-color_23};
    --field-selected-shadow: #{color_variables.$m-color_23};
    display: grid;
    gap: 0.75rem;
}

:global(.dark) .field-editor,
:global(body.dark) .field-editor {
    --field-editor-border: #{color_variables.$m-color_12};
    --field-editor-label: #{color_variables.$m-color_4};
    --field-editor-surface: #{color_variables.$m-color_10};
    --field-editor-input-bg: #{color_variables.$m-color_19};
    --field-grass: #{color_variables.$m-color_19};
    --field-grass-fill: #{color_variables.$m-color_10};
    --field-line-color: #{color_variables.$m-color_14};
    --field-player-color: #{color_variables.$info};
    --field-cone-color: #{color_variables.$warning};
    --field-ball-color: #{color_variables.$m-color_3};
    --field-arrow-color: #{color_variables.$danger};
    --field-xmark-color: #{color_variables.$m-color_3};
    --field-label-color: #{color_variables.$m-color_3};
    --field-selected-shadow: #{color_variables.$m-color_3};
}

.field-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.field-toolbar .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.field-text-input {
    color: var(--field-editor-label);
    max-width: 280px;
    font-size: 0.8125rem;
    font-weight: 600;
}

.field-text-input .form-control {
    background-color: var(--field-editor-input-bg) !important;
    border-color: var(--field-editor-border);
    color: var(--field-editor-label) !important;
}

.field-text-input .form-control:focus {
    background-color: var(--field-editor-input-bg) !important;
    color: var(--field-editor-label) !important;
    box-shadow: none;
}

.field-arrow-controls {
    align-items: center;
    color: var(--field-editor-label);
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
}

.field-arrow-controls .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.soccer-field {
    width: 100%;
    aspect-ratio: 100 / 64;
    min-height: 170px;
    border: 1px solid var(--field-editor-border);
    border-radius: 6px;
    background: var(--field-grass);
    touch-action: none;
}

.field-border,
.field-line {
    stroke: var(--field-line-color);
    stroke-width: 0.45;
}

.field-border {
    fill: var(--field-grass-fill);
}

.fill-none {
    fill: none;
}

.field-dot {
    fill: var(--field-line-color);
}

.field-item {
    cursor: grab;
    outline: none;
}

.field-item.selected .player,
.field-item.selected .cone,
.field-item.selected .ball,
.field-item.selected .arrow-line,
.field-item.selected .xmark-line,
.field-item.selected .field-label {
    filter: drop-shadow(0 0 1.8px var(--field-selected-shadow));
}

.player {
    fill: var(--field-player-color);
}

.cone {
    fill: var(--field-cone-color);
}

.ball {
    fill: var(--field-ball-color);
}

.arrow-line {
    stroke: var(--field-arrow-color);
    stroke-width: 1.1;
    stroke-linecap: round;
}

.arrow-head {
    fill: var(--field-arrow-color);
}

.xmark-line {
    stroke: var(--field-xmark-color);
    stroke-linecap: round;
    stroke-width: 1.05;
}

.field-label {
    fill: var(--field-label-color);
    font-size: 4px;
    font-weight: 700;
    dominant-baseline: middle;
    text-anchor: middle;
}
</style>
