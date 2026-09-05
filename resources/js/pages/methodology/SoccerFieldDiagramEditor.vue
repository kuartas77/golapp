<template>
    <div
        class="field-editor"
        :class="`field-editor--${size}`"
        tabindex="0"
        @keydown="handleKeyDown"
    >
        <div class="field-top-bar">
            <!-- Selector de Recurso Visual (Diagrama / Imagen) -->
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

            <!-- Acciones de estudio (Deshacer, Rehacer, Limpiar, Exportar PNG) -->
            <div v-if="visualMode === 'diagram'" class="field-studio-actions" role="toolbar" aria-label="Acciones de estudio">
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="!canUndo"
                    title="Deshacer (Ctrl+Z)"
                    aria-label="Deshacer"
                    @click="undo"
                >
                    <i class="fa fa-rotate-left fa-width-auto" aria-hidden="true"></i>
                    <span class="d-none d-md-inline">Deshacer</span>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="!canRedo"
                    title="Rehacer (Ctrl+Y)"
                    aria-label="Rehacer"
                    @click="redo"
                >
                    <i class="fa fa-rotate-right fa-width-auto" aria-hidden="true"></i>
                    <span class="d-none d-md-inline">Rehacer</span>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    :disabled="!items.length"
                    title="Limpiar toda la cancha"
                    aria-label="Limpiar cancha"
                    @click="clearAllItems"
                >
                    <i class="fa fa-trash fa-width-auto" aria-hidden="true"></i>
                    <span class="d-none d-md-inline">Limpiar</span>
                </button>
            </div>
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
            <!-- Modos de Interacción (Seleccionar, Lápiz, Borrador) -->
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

            <!-- Ayuda contextual de modo -->
            <div class="field-mode-help" role="status">
                {{ activeModeHelp }}
            </div>

            <!-- Selector de Color Oficial -->
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

            <!-- Barra de Herramientas Tácticas con Presets de Equipos -->
            <div class="field-toolbar-wrapper">
                <!-- Presets rápidos para Equipos -->
                <div class="field-team-presets" role="group" aria-label="Presets rápidos de equipo">
                    <button
                        type="button"
                        class="btn btn-sm btn-team-preset btn-team-preset--blue"
                        title="Añadir jugador numerado de Equipo A"
                        @click="addTeamPlayer('blue', 'A')"
                    >
                        <i class="fa fa-user fa-width-auto" aria-hidden="true"></i>
                        <span>+ Equipo A ({{ nextNumberForColor('blue') }})</span>
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-team-preset btn-team-preset--red"
                        title="Añadir jugador numerado de Equipo B"
                        @click="addTeamPlayer('red', 'B')"
                    >
                        <i class="fa fa-user fa-width-auto" aria-hidden="true"></i>
                        <span>+ Equipo B ({{ nextNumberForColor('red') }})</span>
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-team-preset btn-team-preset--yellow"
                        title="Añadir Portero"
                        @click="addGoalkeeper"
                    >
                        <i class="fa fa-shield fa-width-auto" aria-hidden="true"></i>
                        <span>+ Portero</span>
                    </button>
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
                    <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        :disabled="!selectedKey"
                        @click="removeSelected"
                    >
                        <i class="fa fa-trash fa-width-auto" aria-hidden="true"></i>
                        <span>Eliminar</span>
                    </button>
                </div>
            </div>

            <!-- Controles de Edición de Selección (Texto, Rotación, Duplicar) -->
            <div v-if="selectedItem" class="field-selected-controls">
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

                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm ms-auto"
                    title="Duplicar elemento seleccionado (Ctrl+D)"
                    @click="duplicateSelected"
                >
                    <i class="fa fa-copy fa-width-auto" aria-hidden="true"></i>
                    <span>Duplicar</span>
                </button>
            </div>

            <!-- Cancha de Fútbol Vectorial Profesional -->
            <div class="field-canvas-wrapper">
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
                    <defs>
                        <!-- Patrón de franjas de corte de césped profesional -->
                        <pattern
                            id="fieldGrassStripes"
                            width="10"
                            height="64"
                            patternUnits="userSpaceOnUse"
                        >
                            <rect x="0" y="0" width="5" height="64" class="field-grass-stripe-a" />
                            <rect x="5" y="0" width="5" height="64" class="field-grass-stripe-b" />
                        </pattern>
                    </defs>

                    <!-- Base de cancha con franjas de césped -->
                    <rect x="1" y="1" width="98" height="62" rx="1.5" class="field-border" />
                    <rect x="1" y="1" width="98" height="62" rx="1.5" fill="url(#fieldGrassStripes)" class="field-grass-pattern" />

                    <!-- Cuadrantes de tiro de esquina reglamentarios (Corner Arcs) -->
                    <path d="M 1 3.2 A 2.2 2.2 0 0 0 3.2 1" class="field-line fill-none" />
                    <path d="M 1 60.8 A 2.2 2.2 0 0 1 3.2 63" class="field-line fill-none" />
                    <path d="M 96.8 1 A 2.2 2.2 0 0 0 99 3.2" class="field-line fill-none" />
                    <path d="M 96.8 63 A 2.2 2.2 0 0 1 99 60.8" class="field-line fill-none" />

                    <!-- Línea de medio campo y Círculo central -->
                    <line x1="50" y1="1" x2="50" y2="63" class="field-line" />
                    <circle cx="50" cy="32" r="9" class="field-line fill-none" />
                    <circle cx="50" cy="32" r="1" class="field-dot" />

                    <!-- Áreas de penal (18 yardas) -->
                    <rect x="1" y="18" width="16" height="28" class="field-line fill-none" />
                    <rect x="83" y="18" width="16" height="28" class="field-line fill-none" />

                    <!-- Áreas de meta / chica (6 yardas) -->
                    <rect x="1" y="24" width="6" height="16" class="field-line fill-none" />
                    <rect x="93" y="24" width="6" height="16" class="field-line fill-none" />

                    <!-- Puntos de penal -->
                    <circle cx="11" cy="32" r="1" class="field-dot" />
                    <circle cx="89" cy="32" r="1" class="field-dot" />

                    <!-- Medialunas de área penal (Penalty Arcs) reglamentarias -->
                    <path d="M 17 25.29 A 9 9 0 0 1 17 38.71" class="field-line fill-none" />
                    <path d="M 83 25.29 A 9 9 0 0 0 83 38.71" class="field-line fill-none" />

                    <!-- Porterías con red en los extremos -->
                    <g class="field-goals">
                        <path d="M 1 27 L 0.3 27.5 L 0.3 36.5 L 1 37" class="goal-net" />
                        <line x1="1" y1="27" x2="1" y2="37" class="goal-post" />

                        <path d="M 99 27 L 99.7 27.5 L 99.7 36.5 L 99 37" class="goal-net" />
                        <line x1="99" y1="27" x2="99" y2="37" class="goal-post" />
                    </g>

                    <!-- Elementos Tácticos -->
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
                            <circle :cx="item.x" :cy="item.y" r="2.7" class="player-token-rim" />
                            <text :x="item.x" :y="item.y" class="player-token-label">{{ item.label || '1' }}</text>
                        </g>

                        <path v-else-if="item.type === 'cone'" :d="conePath()" :transform="localElementTransform(item)" class="cone" :style="{ fill: itemColor(item) }" />

                        <g v-else-if="item.type === 'ball'">
                            <circle :cx="item.x" :cy="item.y" r="2.2" class="ball" :style="{ fill: itemColor(item) }" />
                            <circle :cx="item.x" :cy="item.y" r="0.7" class="ball-center" />
                        </g>

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

                        <g v-else-if="item.type === 'xmark'" :transform="`translate(${Number(item.x)} ${Number(item.y)})`">
                            <line x1="-1.3" y1="-1.3" x2="1.3" y2="1.3" class="xmark-line" :style="{ stroke: itemColor(item) }" />
                            <line x1="1.3" y1="-1.3" x2="-1.3" y2="1.3" class="xmark-line" :style="{ stroke: itemColor(item) }" />
                        </g>

                        <path v-else-if="item.type === 'freehand'" :d="freehandPath(item)" class="freehand-line" :style="{ stroke: itemColor(item) }" />

                        <text v-else :x="item.x" :y="item.y" class="field-label" :style="{ fill: itemColor(item) }">{{ item.label || 'Texto' }}</text>

                        <!-- Controles de Rotación para accesorios (cono, valla, bastón) -->
                        <g
                            v-if="itemKey(item, index) === selectedKey && isRotatable(item) && !isDirectional(item)"
                            class="rotation-control"
                            @pointerdown.stop="startRotation(item, index, $event)"
                        >
                            <line :x1="item.x" :y1="item.y - 7" :x2="item.x" :y2="item.y - 5" class="rotation-line" />
                            <circle :cx="item.x" :cy="item.y - 7" r="1.5" class="rotation-handle" />
                            <circle :cx="item.x" :cy="item.y - 7" r="3.2" class="rotation-hit-area" />
                        </g>

                        <!-- Manipulador en la punta para flechas y trayectorias -->
                        <g
                            v-if="itemKey(item, index) === selectedKey && isDirectional(item)"
                            class="tip-handle-control"
                            @pointerdown.stop="startTipDirection(item, index, $event)"
                        >
                            <circle
                                :cx="getArrowTipPoint(item).x"
                                :cy="getArrowTipPoint(item).y"
                                r="1.5"
                                class="tip-handle-circle"
                            />
                            <circle
                                :cx="getArrowTipPoint(item).x"
                                :cy="getArrowTipPoint(item).y"
                                r="3.4"
                                class="tip-handle-hit-area"
                            />
                        </g>
                    </g>
                </svg>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
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
    get: () => (props.modelValue || []).map((item) => ({
        ...item,
        x: item.x !== undefined && item.x !== null ? Number(item.x) : item.x,
        y: item.y !== undefined && item.y !== null ? Number(item.y) : item.y,
        ...(item.rotation !== undefined && item.rotation !== null ? { rotation: Number(item.rotation) } : {}),
    })),
    set: (value) => emit('update:modelValue', value),
})

// Historial Undo / Redo
const history = ref([])
const historyIndex = ref(-1)
const isPerformingHistoryAction = ref(false)

function pushHistoryState(newItems) {
    if (isPerformingHistoryAction.value) {
        return
    }

    const cloned = JSON.parse(JSON.stringify(newItems))
    history.value = history.value.slice(0, historyIndex.value + 1)
    history.value.push(cloned)

    if (history.value.length > 30) {
        history.value.shift()
    } else {
        historyIndex.value++
    }
}

watch(() => props.modelValue, (val) => {
    if (!history.value.length && val && val.length) {
        history.value = [JSON.parse(JSON.stringify(val))]
        historyIndex.value = 0
    }
}, { immediate: true })

const canUndo = computed(() => historyIndex.value > 0)
const canRedo = computed(() => historyIndex.value < history.value.length - 1)

function undo() {
    if (!canUndo.value) return
    isPerformingHistoryAction.value = true
    historyIndex.value--
    items.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]))
    selectedKey.value = null
    isPerformingHistoryAction.value = false
}

function redo() {
    if (!canRedo.value) return
    isPerformingHistoryAction.value = true
    historyIndex.value++
    items.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]))
    selectedKey.value = null
    isPerformingHistoryAction.value = false
}

function commitItemsChange(newItems) {
    items.value = newItems
    pushHistoryState(newItems)
}

const selectedItem = computed(() => items.value.find((item, index) => itemKey(item, index) === selectedKey.value))
const selectedItemAllowsLabel = computed(() => ['player_token', 'text'].includes(selectedItem.value?.type))
const selectedItemIsRotatable = computed(() => isRotatable(selectedItem.value))
const imageModelValue = computed(() => props.imageFile || props.imageUrl || null)
const activeModeHelp = computed(() => ({
    select: 'Selecciona y arrastra figuras. Usa el círculo superior o la punta de las flechas para girar los elementos compatibles.',
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

    const updated = items.value.map((item, index) => itemKey(item, index) === selectedKey.value
        ? { ...item, color }
        : item
    )
    commitItemsChange(updated)
}

function itemKey(item, index) {
    return item.id ?? `item-${index}`
}

function makeId() {
    return `${Date.now()}-${Math.random().toString(16).slice(2)}`
}

function nextNumberForColor(color) {
    const numbersInColor = items.value
        .filter((item) => item.type === 'player_token' && item.color === color)
        .map((item) => parseInt(item.label, 10))
        .filter((n) => !isNaN(n) && n > 0)

    if (!numbersInColor.length) {
        return 1
    }
    return Math.max(...numbersInColor) + 1
}

function addTeamPlayer(color, teamCode) {
    setActiveMode('select')
    selectedColor.value = color

    const nextNum = String(nextNumberForColor(color))
    const existingCount = items.value.filter((i) => i.color === color).length
    const startX = teamCode === 'A' ? 30 + (existingCount % 4) * 5 : 70 - (existingCount % 4) * 5
    const startY = 18 + ((existingCount * 8) % 36)

    const item = {
        id: makeId(),
        type: 'player_token',
        x: Math.max(10, Math.min(90, startX)),
        y: Math.max(10, Math.min(54, startY)),
        label: nextNum,
        color,
    }

    commitItemsChange([...items.value, item])
    selectedKey.value = item.id
}

function addGoalkeeper() {
    setActiveMode('select')
    selectedColor.value = 'yellow'

    const item = {
        id: makeId(),
        type: 'player_token',
        x: 10,
        y: 32,
        label: '1',
        color: 'yellow',
    }

    commitItemsChange([...items.value, item])
    selectedKey.value = item.id
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

    commitItemsChange([...items.value, item])
    selectedKey.value = item.id
}

function duplicateSelected() {
    if (!selectedItem.value) return

    const original = selectedItem.value
    const cloned = {
        ...JSON.parse(JSON.stringify(original)),
        id: makeId(),
        x: Math.min(94, (original.x ?? 50) + 4),
        y: Math.min(60, (original.y ?? 32) + 4),
    }

    if (cloned.type === 'player_token' && !isNaN(parseInt(cloned.label, 10))) {
        cloned.label = String(nextNumberForColor(cloned.color || 'blue'))
    }

    commitItemsChange([...items.value, cloned])
    selectedKey.value = cloned.id
}

function clearAllItems() {
    if (!items.value.length) return
    commitItemsChange([])
    selectedKey.value = null
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
    const updated = items.value.filter((item, index) => itemKey(item, index) !== selectedKey.value)
    commitItemsChange(updated)
    selectedKey.value = null
}

function updateSelectedLabel(event) {
    const updated = items.value.map((item, index) => itemKey(item, index) === selectedKey.value
        ? { ...item, label: event.target.value }
        : item
    )
    commitItemsChange(updated)
}

function rotateSelected(delta) {
    const updated = items.value.map((item, index) => {
        if (itemKey(item, index) !== selectedKey.value || !isRotatable(item)) {
            return item
        }

        return {
            ...item,
            rotation: normalizeRotation(Number(item.rotation ?? 0) + delta),
        }
    })
    commitItemsChange(updated)
}

function resetSelectedRotation() {
    const updated = items.value.map((item, index) => itemKey(item, index) === selectedKey.value && isRotatable(item)
        ? { ...item, rotation: 0 }
        : item
    )
    commitItemsChange(updated)
}

function isRotatable(item) {
    return Boolean(item && rotatableTypes.includes(item.type))
}

function isDirectional(item) {
    return Boolean(item && directionalTypes.includes(item.type))
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
    const wasDraggingOrDrawing = dragState.value || drawingState.value || rotationState.value
    releasePointerCapture(event)
    dragState.value = null
    rotationState.value = null
    drawingState.value = null
    erasingState.value = null

    if (wasDraggingOrDrawing) {
        pushHistoryState(items.value)
    }
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

function startTipDirection(item, index, event) {
    event.preventDefault()
    const key = itemKey(item, index)

    selectedKey.value = key
    rotationState.value = {
        key,
        pointerId: event.pointerId,
        centerX: item.x,
        centerY: item.y,
        isTipAiming: true,
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

    if (state.isTipAiming) {
        const point = eventPoint(event)
        rotation = angleFromCenter(state.centerX, state.centerY, point.x, point.y)
    } else if (state.horizontal) {
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

function getArrowTipPoint(item) {
    const rad = (normalizeRotation(Number(item.rotation ?? 0)) * Math.PI) / 180
    const tipRadius = 5.2
    return {
        x: Number((Number(item.x ?? 50) + Math.cos(rad) * tipRadius).toFixed(2)),
        y: Number((Number(item.y ?? 32) + Math.sin(rad) * tipRadius).toFixed(2)),
    }
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
    const filtered = items.value.filter((item) => item.type !== 'freehand' || !freehandContainsPoint(item, point))
    if (filtered.length !== items.value.length) {
        commitItemsChange(filtered)
    }
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
    return `translate(${Number(item.x ?? 0)} ${Number(item.y ?? 0)}) rotate(${normalizeRotation(Number(item.rotation ?? 0))})`
}

function itemColor(item) {
    return colorPalette[item.color] ?? colorPalette.blue
}

function normalizeRotation(rotation) {
    return ((rotation % 360) + 360) % 360
}

function handleKeyDown(event) {
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
        return
    }

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z' && !event.shiftKey) {
        event.preventDefault()
        undo()
        return
    }

    if ((event.ctrlKey || event.metaKey) && (event.key.toLowerCase() === 'y' || (event.key.toLowerCase() === 'z' && event.shiftKey))) {
        event.preventDefault()
        redo()
        return
    }

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'd' && selectedKey.value) {
        event.preventDefault()
        duplicateSelected()
        return
    }

    if ((event.key === 'Delete' || event.key === 'Backspace') && selectedKey.value) {
        event.preventDefault()
        removeSelected()
        return
    }

    if (event.key === 'Escape') {
        selectedKey.value = null
        return
    }

    if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(event.key) && selectedKey.value) {
        event.preventDefault()
        const step = event.shiftKey ? 2 : 0.5
        const deltaX = event.key === 'ArrowLeft' ? -step : event.key === 'ArrowRight' ? step : 0
        const deltaY = event.key === 'ArrowUp' ? -step : event.key === 'ArrowDown' ? step : 0

        const updated = items.value.map((item, index) => {
            if (itemKey(item, index) !== selectedKey.value) return item
            return {
                ...item,
                x: Math.min(97, Math.max(3, Number(((item.x ?? 50) + deltaX).toFixed(2)))),
                y: Math.min(61, Math.max(3, Number(((item.y ?? 32) + deltaY).toFixed(2)))),
            }
        })
        commitItemsChange(updated)
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown)
})

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped lang="scss">
@use '@/assets/base/color_variables';

.field-editor {
    --field-editor-border: #{color_variables.$m-color_3};
    --field-editor-label: #{color_variables.$dark};
    --field-editor-surface: #{color_variables.$white};
    --field-editor-input-bg: #{color_variables.$white};
    --field-grass: #2d8a4e;
    --field-grass-stripe-a: #2d8a4e;
    --field-grass-stripe-b: #287a45;
    --field-grass-fill: #2d8a4e;
    --field-line-color: rgba(255, 255, 255, 0.9);
    --field-player-color: #{color_variables.$info};
    --field-cone-color: #{color_variables.$warning};
    --field-ball-color: #{color_variables.$m-color_23};
    --field-arrow-color: #{color_variables.$danger};
    --field-xmark-color: #{color_variables.$m-color_23};
    --field-label-color: #{color_variables.$white};
    --field-selected-shadow: #38bdf8;
    --field-panel-bg: #f8fafc;
    display: grid;
    gap: 0.75rem;
    outline: none;
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
    --field-grass: #164024;
    --field-grass-stripe-a: #164024;
    --field-grass-stripe-b: #13361f;
    --field-grass-fill: #164024;
    --field-line-color: rgba(255, 255, 255, 0.75);
    --field-player-color: #{color_variables.$info};
    --field-cone-color: #{color_variables.$warning};
    --field-ball-color: #{color_variables.$m-color_3};
    --field-arrow-color: #{color_variables.$danger};
    --field-xmark-color: #{color_variables.$m-color_3};
    --field-label-color: #{color_variables.$m-color_3};
    --field-selected-shadow: #38bdf8;
    --field-panel-bg: #{color_variables.$m-color_10};
}

.field-top-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.visual-mode-selector {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.4rem;

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
}

.field-studio-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
}

.field-drawing-tools {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
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
    transition: transform 0.1s ease;

    &:hover {
        transform: scale(1.15);
    }
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

.field-toolbar-wrapper {
    display: grid;
    gap: 0.5rem;
}

.field-team-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.btn-team-preset {
    font-weight: 600;

    &--blue {
        background-color: #2563eb;
        border-color: #1d4ed8;
        color: #fff;

        &:hover {
            background-color: #1d4ed8;
            color: #fff;
        }
    }

    &--red {
        background-color: #dc2626;
        border-color: #b91c1c;
        color: #fff;

        &:hover {
            background-color: #b91c1c;
            color: #fff;
        }
    }

    &--yellow {
        background-color: #d4a60f;
        border-color: #b88d0b;
        color: #fff;

        &:hover {
            background-color: #b88d0b;
            color: #fff;
        }
    }
}

.field-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
}

.field-selected-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
    background: var(--field-panel-bg);
    border: 1px solid var(--field-editor-border);
    border-radius: 6px;
    padding: 0.45rem 0.65rem;
}

.field-text-input {
    color: var(--field-editor-label);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0;
    font-size: 0.8125rem;
    font-weight: 600;

    .form-control {
        background-color: var(--field-editor-input-bg) !important;
        border-color: var(--field-editor-border);
        color: var(--field-editor-label) !important;
        max-width: 120px;
    }
}

.field-arrow-controls {
    align-items: center;
    color: var(--field-editor-label);
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    font-size: 0.8125rem;
    font-weight: 600;

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
}

.field-canvas-wrapper {
    position: relative;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.soccer-field {
    width: 100%;
    aspect-ratio: 100 / 64;
    min-height: 170px;
    border: 1px solid var(--field-editor-border);
    border-radius: 8px;
    background: var(--field-grass);
    touch-action: none;
    user-select: none;
    -webkit-user-drag: none;
    display: block;
}

.field-editor--compact .soccer-field {
    max-height: 230px;
    min-height: 130px;
}

.field-border,
.field-line {
    stroke: var(--field-line-color);
    stroke-width: 0.48;
}

.field-border {
    fill: var(--field-grass-fill);
}

.field-grass-stripe-a {
    fill: var(--field-grass-stripe-a);
}

.field-grass-stripe-b {
    fill: var(--field-grass-stripe-b);
}

.field-grass-pattern {
    pointer-events: none;
}

.fill-none {
    fill: none;
}

.field-dot {
    fill: var(--field-line-color);
}

.goal-post {
    stroke: #ffffff;
    stroke-width: 0.7;
    stroke-linecap: round;
}

.goal-net {
    fill: rgba(255, 255, 255, 0.12);
    stroke: rgba(255, 255, 255, 0.45);
    stroke-width: 0.35;
    stroke-dasharray: 0.6 0.6;
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

.field-item.selected {
    .player,
    .player-token,
    .cone,
    .hurdle-frame,
    .hurdle-bar,
    .hurdle-base,
    .training-stick,
    .ball,
    .hoop,
    .arrow-line,
    .tactical-line,
    .xmark-line,
    .freehand-line,
    .field-label,
    .player-token-label {
        filter: drop-shadow(0 0 2px var(--field-selected-shadow));
    }
}

.player {
    stroke: #ffffff;
    stroke-width: 0.35;
}

.player-token {
    stroke: #ffffff;
    stroke-width: 0.55;
}

.player-token-rim {
    fill: none;
    stroke: rgba(255, 255, 255, 0.35);
    stroke-width: 0.25;
}

.player-token-label {
    fill: #ffffff;
    font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
    font-size: 3.5px;
    font-weight: 800;
    dominant-baseline: middle;
    text-anchor: middle;
    pointer-events: none;
    text-shadow: 0 0.5px 1px rgba(0, 0, 0, 0.6);
}

.cone {
    stroke: rgba(0, 0, 0, 0.1);
    stroke-width: 0.2;
}

.ball {
    stroke: #111827;
    stroke-width: 0.3;
}

.ball-center {
    fill: #111827;
}

.hoop {
    fill: none;
    stroke-width: 1.05;
}

.hurdle-frame,
.hurdle-base {
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 0.8;
}

.hurdle-bar {
    stroke-linecap: round;
    stroke-width: 1.15;
}

.training-stick {
    stroke-linecap: round;
    stroke-width: 1.1;
}

.arrow-line {
    stroke-width: 1.1;
    stroke-linecap: round;
}

.arrow-head {
    stroke: none;
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
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.1;
}

.xmark-line {
    stroke-linecap: round;
    stroke-width: 1.05;
}

.field-label {
    font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
    font-size: 4px;
    font-weight: 700;
    dominant-baseline: middle;
    text-anchor: middle;
    text-shadow: 0 0.5px 2px rgba(0, 0, 0, 0.7);
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

.tip-handle-control {
    cursor: crosshair;
}

.tip-handle-circle {
    fill: #38bdf8;
    stroke: #ffffff;
    stroke-width: 0.55;
}

.tip-handle-hit-area {
    fill: transparent;
    pointer-events: all;
}
</style>
