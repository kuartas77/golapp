<template>
    <div class="field-editor">
        <div class="field-toolbar" role="toolbar" aria-label="Herramientas de cancha">
            <button
                v-for="tool in tools"
                :key="tool.type"
                type="button"
                class="btn btn-outline-primary btn-sm"
                @click="addItem(tool.type)"
            >
                <i :class="tool.icon" aria-hidden="true"></i>
                <span>{{ tool.label }}</span>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" :disabled="!selectedId" @click="removeSelected">
                <i class="fa fa-trash fa-width-auto" aria-hidden="true"></i>
                <span>Eliminar</span>
            </button>
        </div>

        <label v-if="selectedItem?.type === 'text'" class="field-text-input">
            Texto
            <input :value="selectedItem.label" type="text" class="form-control form-control-sm" @input="updateSelectedLabel">
        </label>

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
                <g v-else-if="item.type === 'arrow'">
                    <line :x1="item.x - 5" :y1="item.y + 3" :x2="item.x + 5" :y2="item.y - 3" class="arrow-line" />
                    <path :d="arrowHeadPath(item)" class="arrow-head" />
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
    return `M ${item.x + 5} ${item.y - 3} L ${item.x + 1.5} ${item.y - 3.2} L ${item.x + 3.2} ${item.y + 0.1} Z`
}
</script>

<style scoped>
.field-editor {
    display: grid;
    gap: 0.75rem;
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
    max-width: 280px;
    font-size: 0.8125rem;
    font-weight: 600;
}

.soccer-field {
    width: 100%;
    aspect-ratio: 100 / 64;
    min-height: 230px;
    border: 1px solid #d7dee8;
    border-radius: 6px;
    background: #f5faf7;
    touch-action: none;
}

.field-border,
.field-line {
    stroke: #39765b;
    stroke-width: 0.45;
}

.field-border {
    fill: #ecf8ef;
}

.fill-none {
    fill: none;
}

.field-dot {
    fill: #39765b;
}

.field-item {
    cursor: grab;
    outline: none;
}

.field-item.selected .player,
.field-item.selected .cone,
.field-item.selected .ball,
.field-item.selected .arrow-line,
.field-item.selected .field-label {
    filter: drop-shadow(0 0 1.8px #111827);
}

.player {
    fill: #2563eb;
}

.cone {
    fill: #f97316;
}

.ball {
    fill: #111827;
}

.arrow-line {
    stroke: #b91c1c;
    stroke-width: 1.1;
    stroke-linecap: round;
}

.arrow-head {
    fill: #b91c1c;
}

.field-label {
    fill: #111827;
    font-size: 4px;
    font-weight: 700;
    dominant-baseline: middle;
    text-anchor: middle;
}
</style>
