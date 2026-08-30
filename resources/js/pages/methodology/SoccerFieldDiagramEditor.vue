<template>
    <div class="field-editor" :class="`field-editor--${size}`">
        <div class="visual-mode-selector" role="radiogroup" aria-label="Recurso visual de la fase">
            <button
                type="button"
                class="btn btn-sm"
                :class="visualMode === 'diagram' ? 'btn-primary' : 'btn-outline-primary'"
                @click="setVisualMode('diagram')"
            >
                <i class="fa fa-vector-square fa-width-auto" aria-hidden="true"></i>
                <span>Diagrama</span>
            </button>
            <button
                type="button"
                class="btn btn-sm"
                :class="visualMode === 'image' ? 'btn-primary' : 'btn-outline-primary'"
                @click="setVisualMode('image')"
            >
                <i class="fa fa-image fa-width-auto" aria-hidden="true"></i>
                <span>Imagen</span>
            </button>
        </div>

        <FileInputImage
            v-if="visualMode === 'image'"
            :name="imageInputName"
            :model-value="imageModelValue"
            label=""
            alt="Imagen de la fase"
            preview-size="large"
            :max-size-mb="5"
            :allow-remove="true"
            standalone
            @update:model-value="updateImage"
            @removed="removeImage"
        />

        <template v-if="visualMode === 'diagram'">
        <div class="field-drawing-tools" role="toolbar" aria-label="Herramientas de dibujo libre">
            <button
                v-for="mode in interactionModes"
                :key="mode.key"
                type="button"
                class="btn btn-sm"
                :class="activeMode === mode.key ? 'btn-primary' : 'btn-outline-primary'"
                @click="setActiveMode(mode.key)"
            >
                <i :class="mode.icon" aria-hidden="true"></i>
                <span>{{ mode.label }}</span>
            </button>
        </div>

        <div class="field-mode-help" role="status">
            {{ activeModeHelp }}
        </div>

        <div class="field-color-selector" role="radiogroup" aria-label="Color de la figura">
            <span>Color</span>
            <label
                v-for="color in selectableColors"
                :key="color.key"
                class="field-color-option"
                :class="{ 'field-color-option--active': selectedColor === color.key }"
                :title="color.label"
            >
                <input
                    class="visually-hidden"
                    type="radio"
                    :value="color.key"
                    :checked="selectedColor === color.key"
                    :aria-label="`Color ${color.label}`"
                    @change="setSelectedColor(color.key)"
                >
                <span :style="{ backgroundColor: color.value }" aria-hidden="true"></span>
            </label>
        </div>

        <div class="field-toolbar" role="toolbar" aria-label="Figuras y simbología de cancha">
            <button
                v-for="tool in tools"
                :key="tool.key"
                type="button"
                class="btn btn-primary btn-sm"
                @click="addItem(tool)"
            >
                <i :class="tool.icon" aria-hidden="true"></i>
                <span>{{ tool.label }}</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm" :disabled="!selectedKey" @click="removeSelected">
                <i class="fa fa-trash fa-width-auto" aria-hidden="true"></i>
                <span>Eliminar</span>
            </button>
        </div>

        <label v-if="selectedItemAllowsLabel" class="field-text-input">
            {{ selectedItem?.type === 'player_token' ? 'Número' : 'Texto' }}
            <input :value="selectedItem.label" type="text" class="form-control form-control-sm" @input="updateSelectedLabel">
        </label>

        <div v-if="selectedItemIsRotatable" class="field-arrow-controls" aria-label="Orientación del elemento">
            <span>Orientación ({{ Math.round(selectedItem.rotation || 0) }}°)</span>
            <button type="button" class="btn btn-secondary btn-sm" @click="rotateSelected(-15)">
                <i class="fa fa-rotate-left fa-width-auto" aria-hidden="true"></i>
                <span>-15°</span>
            </button>
            <button type="button" class="btn btn-secondary btn-sm" @click="rotateSelected(15)">
                <i class="fa fa-rotate-right fa-width-auto" aria-hidden="true"></i>
                <span>+15°</span>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="resetSelectedRotation">0°</button>
        </div>

        <svg
            ref="svgRef"
            class="soccer-field"
            viewBox="0 0 100 64"
            role="img"
            aria-label="Cancha editable"
            @pointerdown="handleCanvasPointerDown"
            @pointermove="moveSelected"
            @pointerup="stopCanvasInteraction"
            @pointerleave="stopCanvasInteraction"
            @pointercancel="stopCanvasInteraction"
            @lostpointercapture="stopCanvasInteraction"
            @dragstart.prevent
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
                v-for="(item, index) in items"
                :key="itemKey(item, index)"
                class="field-item"
                :class="{ selected: itemKey(item, index) === selectedKey, 'field-item--freehand': item.type === 'freehand' }"
                tabindex="0"
                @pointerdown.stop="handleItemPointerDown(item, index, $event)"
                @click.stop="selectItem(item, index)"
            >
                <circle v-if="item.type === 'player'" :cx="item.x" :cy="item.y" r="2.8" class="player" :style="{ fill: itemColor(item) }" />
                <g v-else-if="item.type === 'player_token'">
                    <circle :cx="item.x" :cy="item.y" r="3.4" class="player-token" :style="{ fill: itemColor(item) }" />
                    <text :x="item.x" :y="item.y" class="player-token-label">{{ item.label || '1' }}</text>
                </g>
                <path v-else-if="item.type === 'cone'" :d="conePath()" :transform="localElementTransform(item)" class="cone" :style="{ fill: itemColor(item) }" />
                <circle v-else-if="item.type === 'ball'" :cx="item.x" :cy="item.y" r="2.2" class="ball" :style="{ fill: itemColor(item) }" />
                <circle v-else-if="item.type === 'hoop'" :cx="item.x" :cy="item.y" r="3.2" class="hoop" :style="{ stroke: itemColor(item) }" />
                <g v-else-if="item.type === 'agility_hurdle'" :transform="localElementTransform(item)">
                    <path d="M -4.2 2.5 L -2.8 -2.5 L 2.8 -2.5 L 4.2 2.5" class="hurdle-frame" :style="{ stroke: itemColor(item) }" />
                    <line x1="-3" y1="-0.7" x2="3" y2="-0.7" class="hurdle-bar" :style="{ stroke: itemColor(item) }" />
                    <line x1="-2.5" y1="2.5" x2="2.5" y2="2.5" class="hurdle-base" :style="{ stroke: itemColor(item) }" />
                </g>
                <g v-else-if="item.type === 'stick'" :transform="localElementTransform(item)">
                    <line x1="-5" y1="0" x2="5" y2="0" class="training-stick" :style="{ stroke: itemColor(item) }" />
                    <circle cx="-5" cy="0" r="0.8" class="training-stick-end" :style="{ fill: itemColor(item) }" />
                    <circle cx="5" cy="0" r="0.8" class="training-stick-end" :style="{ fill: itemColor(item) }" />
                </g>
                <g v-else-if="item.type === 'arrow'" :transform="localElementTransform(item)">
                    <line x1="-4" y1="2.4" x2="3.15" y2="-1.9" class="arrow-line" :style="{ stroke: itemColor(item) }" />
                    <path :d="arrowHeadPath()" class="arrow-head" :style="{ fill: itemColor(item) }" />
                </g>
                <g v-else-if="item.type === 'pass'" :transform="localElementTransform(item)">
                    <line x1="-5" y1="0" x2="4" y2="0" class="tactical-line" :style="{ stroke: itemColor(item) }" />
                    <path :d="straightArrowHeadPath()" class="tactical-head" :style="{ fill: itemColor(item) }" />
                </g>
                <g v-else-if="item.type === 'dribble'" :transform="localElementTransform(item)">
                    <polyline :points="dribblePoints()" class="tactical-line" :style="{ stroke: itemColor(item) }" />
                    <path :d="straightArrowHeadPath()" class="tactical-head" :style="{ fill: itemColor(item) }" />
                </g>
                <g v-else-if="item.type === 'off_ball_run'" :transform="localElementTransform(item)">
                    <line x1="-5" y1="0" x2="4" y2="0" class="tactical-line tactical-line--dashed" :style="{ stroke: itemColor(item) }" />
                    <path :d="straightArrowHeadPath()" class="tactical-head" :style="{ fill: itemColor(item) }" />
                </g>
                <g v-else-if="item.type === 'cross'" :transform="localElementTransform(item)">
                    <path :d="crossPath()" class="tactical-line tactical-line--curve" :style="{ stroke: itemColor(item) }" />
                    <path :d="crossArrowHeadPath()" class="tactical-head" :style="{ fill: itemColor(item) }" />
                </g>
                <g v-else-if="item.type === 'xmark'">
                    <line :x1="item.x - 1.2" :y1="item.y - 1.2" :x2="item.x + 1.2" :y2="item.y + 1.2" class="xmark-line" :style="{ stroke: itemColor(item) }" />
                    <line :x1="item.x + 1.2" :y1="item.y - 1.2" :x2="item.x - 1.2" :y2="item.y + 1.2" class="xmark-line" :style="{ stroke: itemColor(item) }" />
                </g>
                <path v-else-if="item.type === 'freehand'" :d="freehandPath(item)" class="freehand-line" :style="{ stroke: itemColor(item) }" />
                <text v-else :x="item.x" :y="item.y" class="field-label" :style="{ fill: itemColor(item) }">{{ item.label || 'Texto' }}</text>
                <g
                    v-if="itemKey(item, index) === selectedKey && isRotatable(item)"
                    class="rotation-control"
                    @pointerdown.stop="startRotation(item, index, $event)"
                >
                    <line :x1="item.x" :y1="item.y - 7" :x2="item.x" :y2="item.y - 5" class="rotation-line" />
                    <circle :cx="item.x" :cy="item.y - 7" r="1.5" class="rotation-handle" />
                    <circle :cx="item.x" :cy="item.y - 7" r="3.2" class="rotation-hit-area" />
                </g>
            </g>
        </svg>
        </template>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import FileInputImage from '@/components/form/FileInputImage.vue'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'compact'].includes(value),
    },
    visualMode: {
        type: String,
        default: 'diagram',
        validator: (value) => ['diagram', 'image'].includes(value),
    },
    imageFile: {
        type: null,
        default: null,
    },
    imageUrl: {
        type: String,
        default: null,
    },
    imageInputName: {
        type: String,
        default: 'field-diagram-image',
    },
})

const emit = defineEmits(['update:modelValue', 'update:visualMode', 'update:imageFile', 'update:imageUrl', 'update:imageRemoved'])

const svgRef = ref(null)
const selectedKey = ref(null)
const dragState = ref(null)
const rotationState = ref(null)
const activeMode = ref('select')
const drawingState = ref(null)
const erasingState = ref(null)

const colorPalette = {
    blue: '#2563eb',
    red: '#dc2626',
    green: '#16a34a',
    orange: '#f97316',
    black: '#111827',
    yellow: '#d4a60f',
}

const selectableColors = [
    { key: 'red', label: 'Rojo', value: colorPalette.red },
    { key: 'green', label: 'Verde', value: colorPalette.green },
    { key: 'blue', label: 'Azul', value: colorPalette.blue },
    { key: 'yellow', label: 'Amarillo', value: colorPalette.yellow },
]
const selectableColorKeys = selectableColors.map((color) => color.key)
const selectedColor = ref('blue')

const directionalTypes = ['arrow', 'pass', 'dribble', 'off_ball_run', 'cross']
const rotatableTypes = [...directionalTypes, 'cone', 'agility_hurdle', 'stick']

const interactionModes = [
    { key: 'select', label: 'Seleccionar', icon: 'fa fa-mouse-pointer fa-width-auto' },
    { key: 'pencil', label: 'Lápiz', icon: 'fa fa-pencil fa-width-auto' },
    { key: 'eraser', label: 'Borrador', icon: 'fa fa-eraser fa-width-auto' },
]

const tools = [
    { key: 'player', type: 'player', label: 'Jugador', icon: 'fa fa-user fa-width-auto' },
    { key: 'player-token', type: 'player_token', label: 'Ficha', icon: 'fa fa-circle fa-width-auto', labelValue: '1' },
    { key: 'pass', type: 'pass', label: 'Pase', icon: 'fa fa-arrow-right fa-width-auto' },
    { key: 'dribble', type: 'dribble', label: 'Conducción', icon: 'fa fa-wave-square fa-width-auto' },
    { key: 'off-ball-run', type: 'off_ball_run', label: 'Recorrido', icon: 'fa fa-ellipsis-h fa-width-auto' },
    { key: 'cross', type: 'cross', label: 'Centro', icon: 'fa fa-share fa-width-auto' },
    { key: 'cone', type: 'cone', label: 'Cono', icon: 'fa fa-warning fa-width-auto' },
    { key: 'agility-hurdle', type: 'agility_hurdle', label: 'Valla', icon: 'fa fa-bars fa-width-auto' },
    { key: 'stick', type: 'stick', label: 'Bastón', icon: 'fa fa-minus fa-width-auto' },
    { key: 'ball', type: 'ball', label: 'Balón', icon: 'fa fa-futbol fa-width-auto' },
    { key: 'hoop', type: 'hoop', label: 'Aro', icon: 'fa fa-circle-o fa-width-auto' },
    { key: 'arrow', type: 'arrow', label: 'Flecha', icon: 'fa fa-arrow-right fa-width-auto' },
    { key: 'xmark', type: 'xmark', label: 'X', icon: 'fa fa-xmark fa-width-auto' },
    { key: 'text', type: 'text', label: 'Texto', icon: 'fa fa-font fa-width-auto', labelValue: 'Texto' },
]

const items = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
})

const selectedItem = computed(() => items.value.find((item, index) => itemKey(item, index) === selectedKey.value))
const selectedItemAllowsLabel = computed(() => ['player_token', 'text'].includes(selectedItem.value?.type))
const selectedItemIsRotatable = computed(() => isRotatable(selectedItem.value))
const imageModelValue = computed(() => props.imageFile || props.imageUrl || null)
const activeModeHelp = computed(() => ({
    select: 'Selecciona y arrastra figuras. Usa el círculo superior o Shift + arrastre para girar los elementos compatibles.',
    pencil: 'Dibuja trazos libres sobre la cancha. Los trazos se guardan con la planificación.',
    eraser: 'Borra trazos completos hechos con el lápiz al tocarlos; no borra partes del trazo ni elimina jugadores, flechas, fichas o texto.',
})[activeMode.value])

function setVisualMode(mode) {
    emit('update:visualMode', mode)
}

function updateImage(file) {
    emit('update:imageFile', file)
    emit('update:imageRemoved', false)
}

function removeImage() {
    emit('update:imageFile', null)
    emit('update:imageUrl', null)
    emit('update:imageRemoved', true)
}

function normalizedSelectedColor() {
    return selectableColorKeys.includes(selectedColor.value) ? selectedColor.value : 'blue'
}

function setSelectedColor(color) {
    if (!selectableColorKeys.includes(color)) {
        return
    }

    selectedColor.value = color

    if (!selectedKey.value) {
        return
    }

    items.value = items.value.map((item, index) => itemKey(item, index) === selectedKey.value
        ? { ...item, color }
        : item
    )
}

function itemKey(item, index) {
    return item.id ?? `item-${index}`
}

function makeId() {
    return `${Date.now()}-${Math.random().toString(16).slice(2)}`
}

function addItem(tool) {
    setActiveMode('select')

    const item = {
        id: makeId(),
        type: tool.type,
        x: 50,
        y: 32,
        label: tool.labelValue ?? '',
        color: normalizedSelectedColor(),
        ...(rotatableTypes.includes(tool.type) ? { rotation: 0 } : {}),
    }

    items.value = [...items.value, item]
    selectedKey.value = item.id
}

function setActiveMode(mode) {
    activeMode.value = mode
    dragState.value = null
    rotationState.value = null
    drawingState.value = null
    erasingState.value = null

    if (mode !== 'select') {
        selectedKey.value = null
    }
}

function removeSelected() {
    items.value = items.value.filter((item, index) => itemKey(item, index) !== selectedKey.value)
    selectedKey.value = null
}

function updateSelectedLabel(event) {
    items.value = items.value.map((item, index) => itemKey(item, index) === selectedKey.value
        ? { ...item, label: event.target.value }
        : item
    )
}

function rotateSelected(delta) {
    items.value = items.value.map((item, index) => {
        if (itemKey(item, index) !== selectedKey.value || !isRotatable(item)) {
            return item
        }

        return {
            ...item,
            rotation: normalizeRotation(Number(item.rotation ?? 0) + delta),
        }
    })
}

function resetSelectedRotation() {
    items.value = items.value.map((item, index) => itemKey(item, index) === selectedKey.value && isRotatable(item)
        ? { ...item, rotation: 0 }
        : item
    )
}

function isRotatable(item) {
    return Boolean(item && rotatableTypes.includes(item.type))
}

function handleCanvasPointerDown(event) {
    event.preventDefault()

    if (activeMode.value === 'pencil') {
        startFreehand(event)
        return
    }

    if (activeMode.value === 'eraser') {
        startErasing(event)
        return
    }

    selectedKey.value = null
}

function handleItemPointerDown(item, index, event) {
    event.preventDefault()

    if (activeMode.value === 'pencil') {
        startFreehand(event)
        return
    }

    if (activeMode.value === 'eraser') {
        startErasing(event)
        return
    }

    if (event.shiftKey && isRotatable(item)) {
        startHorizontalRotation(item, index, event)
        return
    }

    startDrag(item, index, event)
}

function selectItem(item, index) {
    if (activeMode.value !== 'select') {
        return
    }

    selectedKey.value = itemKey(item, index)
    selectedColor.value = selectableColorKeys.includes(item.color) ? item.color : null
}

function startDrag(item, index, event) {
    event.preventDefault()

    const key = itemKey(item, index)

    selectedKey.value = key
    dragState.value = {
        key,
        pointerId: event.pointerId,
    }
    event.currentTarget.setPointerCapture?.(event.pointerId)
}

function moveSelected(event) {
    if (drawingState.value) {
        event.preventDefault()

        if (!isPointerActive(event)) {
            stopCanvasInteraction(event)
            return
        }

        updateFreehand(event)
        return
    }

    if (erasingState.value) {
        event.preventDefault()

        if (!isPointerActive(event)) {
            stopCanvasInteraction(event)
            return
        }

        eraseFreehandAtEvent(event)
        return
    }

    if (rotationState.value) {
        event.preventDefault()

        if (!isPointerActive(event)) {
            stopCanvasInteraction(event)
            return
        }

        updateRotation(event)
        return
    }

    if (!dragState.value || !svgRef.value) {
        return
    }

    event.preventDefault()

    const { x, y } = eventPoint(event)

    items.value = items.value.map((item, index) => itemKey(item, index) === dragState.value.key ? { ...item, x, y } : item)
}

function stopCanvasInteraction(event = null) {
    releasePointerCapture(event)
    dragState.value = null
    rotationState.value = null
    drawingState.value = null
    erasingState.value = null
}

function startRotation(item, index, event) {
    event.preventDefault()
    const point = eventPoint(event)
    const key = itemKey(item, index)

    selectedKey.value = key
    rotationState.value = {
        key,
        pointerId: event.pointerId,
        centerX: item.x,
        centerY: item.y,
        startAngle: angleFromCenter(item.x, item.y, point.x, point.y),
        initialRotation: Number(item.rotation ?? 0),
    }
    event.currentTarget.setPointerCapture?.(event.pointerId)
}

function startHorizontalRotation(item, index, event) {
    const key = itemKey(item, index)

    selectedKey.value = key
    rotationState.value = {
        key,
        pointerId: event.pointerId,
        horizontal: true,
        startClientX: event.clientX,
        initialRotation: Number(item.rotation ?? 0),
    }
    event.currentTarget.setPointerCapture?.(event.pointerId)
}

function updateRotation(event) {
    const state = rotationState.value

    if (!state) {
        return
    }

    let rotation = state.initialRotation

    if (state.horizontal) {
        rotation += (event.clientX - state.startClientX) * 0.8
    } else {
        const point = eventPoint(event)
        rotation += normalizeSignedAngle(
            angleFromCenter(state.centerX, state.centerY, point.x, point.y) - state.startAngle
        )
    }

    items.value = items.value.map((item, index) => itemKey(item, index) === state.key
        ? { ...item, rotation: normalizeRotation(rotation) }
        : item
    )
}

function angleFromCenter(centerX, centerY, x, y) {
    return Math.atan2(y - centerY, x - centerX) * (180 / Math.PI)
}

function normalizeSignedAngle(angle) {
    let result = angle % 360

    if (result > 180) result -= 360
    if (result < -180) result += 360

    return result
}

function startFreehand(event) {
    if (!svgRef.value) {
        return
    }

    event.preventDefault()
    stopCanvasInteraction()

    const point = eventPoint(event)
    const item = {
        id: makeId(),
        type: 'freehand',
        points: [point],
        color: normalizedSelectedColor(),
        strokeWidth: 1.1,
    }

    selectedKey.value = null
    items.value = [...items.value, item]
    drawingState.value = { key: item.id, pointerId: event.pointerId }
    svgRef.value.setPointerCapture?.(event.pointerId)
}

function updateFreehand(event) {
    if (!drawingState.value || !svgRef.value) {
        return
    }

    const point = eventPoint(event)

    items.value = items.value.map((item, index) => {
        if (itemKey(item, index) !== drawingState.value.key || item.type !== 'freehand') {
            return item
        }

        const points = Array.isArray(item.points) ? item.points : []
        const lastPoint = points.at(-1)

        if (lastPoint && distanceBetween(lastPoint, point) < 0.45) {
            return item
        }

        return { ...item, points: [...points, point] }
    })
}

function eraseFreehandAtEvent(event) {
    if (!svgRef.value) {
        return
    }

    const point = eventPoint(event)
    items.value = items.value.filter((item) => item.type !== 'freehand' || !freehandContainsPoint(item, point))
}

function startErasing(event) {
    event.preventDefault()
    stopCanvasInteraction()
    erasingState.value = { pointerId: event.pointerId }
    selectedKey.value = null
    svgRef.value?.setPointerCapture?.(event.pointerId)
    eraseFreehandAtEvent(event)
}

function eventPoint(event) {
    const point = svgRef.value.createSVGPoint()
    point.x = event.clientX
    point.y = event.clientY
    const svgPoint = point.matrixTransform(svgRef.value.getScreenCTM().inverse())

    return {
        x: Math.min(97, Math.max(3, Number(svgPoint.x.toFixed(2)))),
        y: Math.min(61, Math.max(3, Number(svgPoint.y.toFixed(2)))),
    }
}

function isPointerActive(event) {
    return event.buttons === undefined || event.buttons > 0 || event.pointerType === 'touch'
}

function releasePointerCapture(event) {
    const pointerId = event?.pointerId ?? rotationState.value?.pointerId ?? drawingState.value?.pointerId ?? erasingState.value?.pointerId ?? dragState.value?.pointerId

    if (pointerId === undefined || !svgRef.value?.hasPointerCapture?.(pointerId)) {
        return
    }

    svgRef.value.releasePointerCapture?.(pointerId)
}

function conePath() {
    return 'M 0 -3 L -3 3 L 3 3 Z'
}

function arrowHeadPath() {
    return 'M 4.6 -2.75 L 1.85 -2.95 L 3.15 -0.55 Z'
}

function straightArrowHeadPath() {
    return 'M 5.1 0 L 2.6 -1.45 L 2.6 1.45 Z'
}

function crossArrowHeadPath() {
    return 'M 5.1 -3.1 L 2.55 -5 L 2.8 -1.1 Z'
}

function dribblePoints() {
    return [
        [-5, 1],
        [-3.3, 1],
        [-3.3, -1],
        [-1.6, -1],
        [-1.6, 1],
        [0.1, 1],
        [0.1, -1],
        [1.8, -1],
        [1.8, 0],
        [3.6, 0],
    ].map((point) => point.join(',')).join(' ')
}

function crossPath() {
    return 'M -5 3 Q -0.8 -4.3 4.1 -3'
}

function freehandPath(item) {
    const points = Array.isArray(item.points) ? item.points : []

    if (!points.length) {
        return ''
    }

    return points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`).join(' ')
}

function freehandContainsPoint(item, point) {
    const points = Array.isArray(item.points) ? item.points : []

    if (points.length === 1) {
        return distanceBetween(points[0], point) <= 2.2
    }

    return points.some((currentPoint, index) => {
        if (index === 0) {
            return false
        }

        return distanceToSegment(point, points[index - 1], currentPoint) <= 2.2
    })
}

function distanceBetween(a, b) {
    return Math.hypot(a.x - b.x, a.y - b.y)
}

function distanceToSegment(point, start, end) {
    const segmentLength = distanceBetween(start, end)

    if (segmentLength === 0) {
        return distanceBetween(point, start)
    }

    const ratio = Math.max(0, Math.min(1, (
        ((point.x - start.x) * (end.x - start.x)) + ((point.y - start.y) * (end.y - start.y))
    ) / (segmentLength ** 2)))

    return distanceBetween(point, {
        x: start.x + (ratio * (end.x - start.x)),
        y: start.y + (ratio * (end.y - start.y)),
    })
}

function localElementTransform(item) {
    return `translate(${item.x} ${item.y}) rotate(${normalizeRotation(Number(item.rotation ?? 0))})`
}

function itemColor(item) {
    return colorPalette[item.color] ?? colorPalette.blue
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

.field-editor--compact {
    gap: 0.5rem;
}

:global(.dark .field-editor),
:global(body.dark .field-editor) {
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

.visual-mode-selector {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.visual-mode-selector .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.field-drawing-tools {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.field-drawing-tools .btn,
.field-toolbar .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.field-mode-help {
    background: var(--field-editor-surface);
    border: 1px solid var(--field-editor-border);
    border-radius: 6px;
    color: var(--field-editor-label);
    font-size: 0.8125rem;
    line-height: 1.35;
    padding: 0.5rem 0.65rem;
}

.field-editor--compact .field-mode-help {
    font-size: 0.75rem;
    padding: 0.35rem 0.5rem;
}

.field-color-selector {
    align-items: center;
    color: var(--field-editor-label);
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
}

.field-color-option {
    align-items: center;
    border: 2px solid transparent;
    border-radius: 50%;
    cursor: pointer;
    display: inline-flex;
    padding: 2px;
}

.field-color-option > span {
    border: 1px solid var(--field-editor-border);
    border-radius: 50%;
    display: block;
    height: 1.35rem;
    width: 1.35rem;
}

.field-color-option--active,
.field-color-option:focus-within {
    border-color: var(--field-selected-shadow);
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
    user-select: none;
    -webkit-user-drag: none;
}

.field-editor--compact .soccer-field {
    max-height: 230px;
    min-height: 130px;
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
    user-select: none;
    -webkit-user-drag: none;
}

.field-item--freehand {
    cursor: crosshair;
    pointer-events: none;
}

.field-item.selected .player,
.field-item.selected .player-token,
.field-item.selected .cone,
.field-item.selected .hurdle-frame,
.field-item.selected .hurdle-bar,
.field-item.selected .hurdle-base,
.field-item.selected .training-stick,
.field-item.selected .ball,
.field-item.selected .hoop,
.field-item.selected .arrow-line,
.field-item.selected .tactical-line,
.field-item.selected .xmark-line,
.field-item.selected .freehand-line,
.field-item.selected .field-label,
.field-item.selected .player-token-label {
    filter: drop-shadow(0 0 1.8px var(--field-selected-shadow));
}

.player {
    fill: var(--field-player-color);
}

.cone {
    fill: var(--field-cone-color);
}

.hurdle-frame,
.hurdle-base {
    fill: none;
    stroke: var(--field-cone-color);
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 0.8;
}

.hurdle-bar {
    stroke: var(--field-cone-color);
    stroke-linecap: round;
    stroke-width: 1.15;
}

.training-stick {
    stroke: #d4a60f;
    stroke-linecap: round;
    stroke-width: 1.1;
}

.training-stick-end {
    fill: #d4a60f;
}

.rotation-control {
    cursor: grab;
}

.rotation-line {
    stroke: var(--field-selected-shadow);
    stroke-dasharray: 0.8 0.8;
    stroke-width: 0.45;
}

.rotation-handle {
    fill: var(--field-editor-surface);
    stroke: var(--field-selected-shadow);
    stroke-width: 0.55;
}

.rotation-hit-area {
    fill: transparent;
    pointer-events: all;
}

.ball {
    fill: var(--field-ball-color);
}

.hoop {
    fill: none;
    stroke-width: 1.05;
}

.arrow-line {
    stroke: var(--field-arrow-color);
    stroke-width: 1.1;
    stroke-linecap: round;
}

.arrow-head {
    fill: var(--field-arrow-color);
}

.tactical-line {
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 0.95;
}

.tactical-line--dashed {
    stroke-dasharray: 1.6 1.5;
}

.tactical-line--curve {
    stroke-width: 0.9;
}

.tactical-head {
    stroke: none;
}

.freehand-line {
    fill: none;
    stroke: var(--field-ball-color);
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.1;
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

.player-token-label {
    fill: #{color_variables.$white};
    font-size: 3.5px;
    font-weight: 800;
    dominant-baseline: middle;
    text-anchor: middle;
}
</style>
