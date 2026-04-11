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
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'

const props = defineProps({
    formation: { type: String, required: true },
    formationsMap: { type: Object, required: true },
    playersField: { type: Array, required: true },
    availablePlayers: { type: Array, default: () => [] },
    playerCount: { type: Number, default: 11 }, // Nuevo prop para número de jugadores
    includeGoalkeeper: { type: Boolean, default: true } // Si incluye portero o no
})

const emits = defineEmits(['assign-player', 'unassign-player', 'reset-lineup', 'update-positions'])

const canvas = ref(null)
let ctx = null
const fieldImg = new Image()
fieldImg.src = '/img/field-vertical.webp'

const canvasWidth = ref(0)
const canvasHeight = ref(0)
const hoveredKey = ref(null)
const canvasStatus = ref('Arrastra jugadores al campo o mueve un titular dentro de la cancha.')

const assignedCount = computed(() =>
    posKeys.value.reduce((total, key) => total + (positions.value[key]?.assigned ? 1 : 0), 0)
)
const openSlots = computed(() => Math.max(props.playerCount - assignedCount.value, 0))

// POSICIONES DINÁMICAS según el número de jugadores
const posKeys = computed(() => {
    const keys = []
    if (props.includeGoalkeeper) {
        keys.push('GK')
    }

    const fieldPlayers = props.playerCount - (props.includeGoalkeeper ? 1 : 0)
    for (let i = 1; i <= fieldPlayers; i++) {
        keys.push(`P${i}`)
    }

    return keys
})

// Inicializar posiciones reactivamente
const positions = ref({})

// Watcher para inicializar/actualizar posiciones cuando cambie playerCount
watch([posKeys], () => {
    initializePositions()
}, { immediate: true })

function initializePositions() {
    const newPositions = {}
    posKeys.value.forEach(k => {
        // Mantener posición existente si ya existe, sino crear nueva
        newPositions[k] = positions.value[k] || { x: 0, y: 0, assigned: null }
    })
    positions.value = newPositions
}

// Dragging internos
let dragging = false
let dragKey = null
let dragOffsetX = 0
let dragOffsetY = 0

// Cuando la imagen carga ajustamos canvas pixel size
fieldImg.onload = () => {
    // Usar las dimensiones naturales de la imagen para el canvas
    canvasWidth.value = fieldImg.naturalWidth
    canvasHeight.value = fieldImg.naturalHeight
    if (canvas.value) {
        canvas.value.width = canvasWidth.value
        canvas.value.height = canvasHeight.value
        applyFormation(props.formation)
        drawField()
    }
}

// helpers
function parseFormationString(str) {
    if (!str) return null
    const parts = str.split('-').map(s => Number(s))
    if (parts.some(n => Number.isNaN(n) || n < 0)) return null
    return parts
}

// Agregar esta función para determinar el rol específico
function getSpecificRole(position) {
    const { type, line, order, x } = position;
    const canvasCenterX = canvasWidth.value / 2;

    // Portero
    if (position.key === 'GK') return 'Portero';

    // Defensas
    if (type === 'defense') {
        if (line === 0) { // Primera línea defensiva
            const isLeft = x < canvasCenterX - 50;
            const isRight = x > canvasCenterX + 50;
            const isCenter = !isLeft && !isRight;

            if (isCenter) return 'Defensa(Central)';
            if (isLeft) return 'Defensa(Izquierdo)';
            if (isRight) return 'Defensa(Derecho)';
        }
        return 'Defensa';
    }

    // Mediocampistas/Volantes
    if (type.includes('mid')) {
        // Determinar si es extremo o central basado en posición X
        const isWide = Math.abs(x - canvasCenterX) > canvasWidth.value * 0.25;
        const isLeftWide = x < canvasCenterX - 100;
        const isRightWide = x > canvasCenterX + 100;

        if (type === 'mid-defensive') {
            if (isWide) {
                return isLeftWide ? 'Volante(Defensivo Izquierdo)' : 'Volante(Defensivo Derecho)';
            }
            return 'Volante(Defensivo Central)';
        }

        if (type === 'mid-offensive') {
            if (isWide) {
                return isLeftWide ? 'Volante(Ofensivo Izquierdo)' : 'Volante(Ofensivo Derecho)';
            }
            return 'Volante(Ofensivo Central)';
        }

        // Medio campo general
        if (isWide) {
            if (isLeftWide) return 'Volante(Extremo Izquierdo)';
            if (isRightWide) return 'Volante(Extremo Derecho)';
        }

        // Distinguir por líneas en mediocampo
        if (line === 1) return 'Volante(Primera línea)';
        if (line === 2) return 'Volante(Segunda línea)';

        return 'Volante(Central)';
    }

    // Delanteros
    if (type === 'attack') {
        const isLeft = x < canvasCenterX - 30;
        const isRight = x > canvasCenterX + 30;

        if (isLeft) return 'Delantero(Izquierdo)';
        if (isRight) return 'Delantero(Derecho)';
        return 'Delantero(Central)';
    }

    return 'Jugador';
}

// Función para actualizar roles específicos en todas las posiciones
function updateSpecificRoles() {
    for (const key of posKeys.value) {
        if (positions.value[key]) {
            positions.value[key].specificRole = getSpecificRole(positions.value[key])
        }
    }
}

// aplica la formacion actual a las posiciones
function applyFormation(formationStr) {
    const parts = parseFormationString(formationStr)
    const scheme = props.formationsMap[formationStr] || parts
    if (!scheme) return

    const fieldPlayers = props.playerCount - (props.includeGoalkeeper ? 1 : 0)
    const totalFormationPlayers = scheme.reduce((sum, num) => sum + num, 0)

    if (totalFormationPlayers !== fieldPlayers) {
        console.warn(`La formación ${formationStr} (${totalFormationPlayers} jugadores) no coincide con el número de jugadores de campo (${fieldPlayers})`)
        adjustFormationToPlayerCount(scheme, fieldPlayers)
    }

    // PORTERO
    if (props.includeGoalkeeper) {
        positions.value['GK'] = {
            ...positions.value['GK'],
            x: canvasWidth.value / 2,
            y: canvasHeight.value * 0.82,
            key: 'GK'
        }
    }

    // DISTRIBUCIÓN DINÁMICA SEGÚN LA FORMACIÓN
    const lines = scheme.length
    let index = props.includeGoalkeeper ? 1 : 0 // índice inicial

    // Determinar el tipo de cada línea basado en la formación
    const lineTypes = getLineTypes(scheme.length)

    for (let line = 0; line < lines; line++) {
        const count = scheme[line]
        const lineType = lineTypes[line]

        let yFrac;
        switch (lineType) {
            case 'defense':
                yFrac = 0.62;
                break
            case 'mid-defensive':
                yFrac = 0.48;
                break
            case 'midfield':
                yFrac = 0.3;
                break
            case 'mid-offensive':
                yFrac = 0.35;
                break
            case 'attack':
                yFrac = 0.15;
                break
            default:
                yFrac = 0.75 - (line / Math.max(1, lines - 1)) * 0.5
        }

        // PERSPECTIVA 3D: función no lineal para mayor realismo
        const perspectiveFactor = Math.pow((yFrac - 0.15) / 0.5, 0.7)
        const minMargin = 0.08
        const maxMargin = 0.30
        const horizontalMarginFraction = maxMargin - (maxMargin - minMargin) * perspectiveFactor

        const leftMargin = canvasWidth.value * horizontalMarginFraction
        const rightMargin = canvasWidth.value * horizontalMarginFraction
        const usableWidth = canvasWidth.value - leftMargin - rightMargin

        // Distribución ligeramente curvada para mayor realismo
        for (let i = 0; i < count; i++) {
            if (index >= posKeys.value.length) break

            const positionRatio = (i + 1) / (count + 1)
            const curvedPosition = positionRatio
            const x = leftMargin + usableWidth * curvedPosition

            const posKey = posKeys.value[index]
            positions.value[posKey] = {
                ...positions.value[posKey],
                x: x,
                y: canvasHeight.value * yFrac,
                type: lineType,
                line: line,
                order: i,
                key: posKey
            }

            index++
        }
    }

    // Si quedaron posiciones vacías (por ajustes en la formación)
    while (index < posKeys.value.length) {
        // Colocar posiciones sobrantes en el medio campo
        const posKey = posKeys.value[index]
        positions.value[posKey] = {
            ...positions.value[posKey],
            x: canvasWidth.value * 0.5,
            y: canvasHeight.value * 0.5,
            type: 'midfield',
            line: Math.floor(lines / 2),
            order: index,
            key: posKey
        }
        index++
    }

    // Calcular roles específicos para todas las posiciones
    updateSpecificRoles()

    emits('update-positions', getPositionsSnapshot())
    drawField()
}

// Función para determinar los tipos de línea basado en el número de líneas
function getLineTypes(lineCount) {
    const types = []

    if (lineCount === 1) {
        // Una sola línea - todo el medio campo
        return ['midfield']
    } else if (lineCount === 2) {
        // Dos líneas - defensa y ataque
        return ['defense', 'attack']
    } else if (lineCount === 3) {
        // Tres líneas - defensa, medio, ataque
        return ['defense', 'midfield', 'attack']
    } else if (lineCount === 4) {
        // Cuatro líneas - defensa, medio-defensivo, medio, ataque
        return ['defense', 'mid-defensive', 'midfield', 'attack']
    } else if (lineCount === 5) {
        // Cinco líneas - defensa, medio-defensivo, medio, medio-ofensivo, ataque
        return ['defense', 'mid-defensive', 'midfield', 'mid-offensive', 'attack']
    } else {
        // Para más de 5 líneas, distribución progresiva de abajo a arriba
        for (let i = 0; i < lineCount; i++) {
            if (i === 0) types.push('defense')
            else if (i === lineCount - 1) types.push('attack')
            else types.push('midfield')
        }
        return types
    }
}

// Función para ajustar automáticamente la formación al número de jugadores
function adjustFormationToPlayerCount(scheme, targetPlayerCount) {
    const currentTotal = scheme.reduce((sum, num) => sum + num, 0)
    const difference = targetPlayerCount - currentTotal

    if (difference === 0) return scheme

    if (difference > 0) {
        // Añadir jugadores a las líneas existentes
        let remaining = difference
        let lineIndex = 0

        while (remaining > 0) {
            // Añadir un jugador a cada línea de forma circular
            scheme[lineIndex % scheme.length]++
            remaining--
            lineIndex++
        }
    } else {
        // Remover jugadores de las líneas existentes
        let remaining = Math.abs(difference)
        let lineIndex = scheme.length - 1 // Empezar desde la última línea

        while (remaining > 0 && lineIndex >= 0) {
            if (scheme[lineIndex] > 1) {
                // Solo reducir si hay más de 1 jugador en la línea
                scheme[lineIndex]--
                remaining--
            }
            lineIndex--

            // Si llegamos al inicio, reiniciar desde el final
            if (lineIndex < 0) lineIndex = scheme.length - 1
        }
    }

    console.log(`Formación ajustada automáticamente: ${scheme.join('-')}`)
    return scheme
}

// devuelve la posición más cercana dentro de radio límite
function findNearestPosition(x, y, radius = 40) {
    let best = null
    let bestDist = Infinity

    // Radio dinámico basado en la densidad de jugadores
    const positionsInArea = posKeys.value.filter(k => {
        const p = positions.value[k]
        const dx = p.x - x
        const dy = p.y - y
        return Math.sqrt(dx * dx + dy * dy) < radius * 2
    }).length

    // Ajustar radio según densidad (mayor densidad = radio más pequeño)
    const densityFactor = Math.max(0.5, 1 - (positionsInArea / 20))
    const adjustedRadius = radius * densityFactor

    for (const k of posKeys.value) {
        const p = positions.value[k]

        // Penalizar posiciones con jugadores asignados
        const penalty = p.assigned ? 20 : 0

        const dx = p.x - x
        const dy = p.y - y
        const d = Math.sqrt(dx * dx + dy * dy) + penalty

        if (d < bestDist && d <= adjustedRadius) {
            bestDist = d
            best = k
        }
    }
    return best
}

function toCanvasPoint(e) {
    const rect = canvas.value.getBoundingClientRect()
    const scaleX = canvasWidth.value / rect.width
    const scaleY = canvasHeight.value / rect.height

    return {
        x: (e.clientX - rect.left) * scaleX,
        y: (e.clientY - rect.top) * scaleY
    }
}

// asignar jugador a posKey
function assignPlayerToPos(playerObj, posKey) {
    if (!playerObj || !posKey) return

    // Verificar si el jugador ya está asignado en otra posición
    const existingPosition = findPlayerPosition(playerObj.id)
    if (existingPosition && existingPosition !== posKey) {
        const prevPlayer = positions.value[existingPosition].assigned
        positions.value[existingPosition].assigned = null
        emits('unassign-player', { player: prevPlayer, posKey: existingPosition })
    }

    const positionRole = positions.value[posKey].specificRole
    const prev = positions.value[posKey].assigned
    if (prev && (!playerObj.id || prev.id !== playerObj.id)) {
        emits('unassign-player', { player: prev, posKey })
    }

    const playerData = playerObj.player || playerObj
    const clone = {
        ...playerObj,
        player: playerData,
        id: playerData.id ?? playerObj.id ?? playerObj.inscription_id,
        inscription_id: playerObj.inscription_id ?? null,
        unique_code: playerData.unique_code ?? playerObj.unique_code ?? null,
        name: playerObj.name || playerData.full_names || `${playerData.last_names || ''} ${playerData.names || ''}`.trim(),
        img: playerObj.img || playerData.photo_url_public || playerData.photo_url || playerData.photo_local || null,
        imgLoading: false, // Bandera para controlar carga de imagen
        position: positionRole
    }

    // Solo cargar imagen si existe y es una URL válida
    if (clone.img && typeof clone.img === 'string' && clone.img.trim() !== '') {
        clone.imgObj = null // Inicializar como null

        // Cargar imagen de forma asíncrona
        setTimeout(() => {
            if (positions.value[posKey]?.assigned?.id === clone.id) {
                const img = new Image()
                img.onload = () => {
                    if (positions.value[posKey]?.assigned?.id === clone.id) {
                        positions.value[posKey].assigned.imgObj = img
                        drawField()
                    }
                }
                img.onerror = () => {
                    console.warn('No se pudo cargar la imagen:', clone.img)
                    if (positions.value[posKey]?.assigned?.id === clone.id) {
                        positions.value[posKey].assigned.imgObj = null
                        drawField()
                    }
                }
                img.src = clone.img
            }
        }, 0)
    } else {
        clone.imgObj = null
    }

    positions.value[posKey].assigned = clone
    emits('assign-player', { player: clone, posKey })
    emits('update-positions', getPositionsSnapshot())
    drawField()
}

// Función auxiliar para encontrar en qué posición está un jugador
function findPlayerPosition(playerId) {
    for (const k of posKeys.value) {
        if (positions.value[k].assigned && positions.value[k].assigned.id === playerId) {
            return k
        }
    }
    return null
}

// cuando sueltas desde PlayerList sobre canvas
function onDrop(e) {
    const raw = e.dataTransfer.getData('application/json')
    if (!raw) return

    try {
        const player = JSON.parse(raw)
        const { x, y } = toCanvasPoint(e)

        // Encontrar la posición más cercana con radio más pequeño para mayor precisión
        const nearest = findNearestPosition(x, y, 40) // Reducir el radio para más precisión

        if (nearest) {
            assignPlayerToPos(player, nearest)
            canvasStatus.value = `${player.name} asignado a ${positions.value[nearest].specificRole || nearest}.`
        } else {
            canvasStatus.value = 'Suelta el jugador cerca de un punto táctico para asignarlo.'
        }
    } catch (error) {
        console.error('Error processing drop:', error)
    }
}

// DnD interno: mover jugador en el canvas
function startDrag(e) {
    const { x, y } = toCanvasPoint(e)

    for (const k of posKeys.value) {
        // No permitir arrastrar portero si está incluido
        if (props.includeGoalkeeper && k === 'GK') continue

        const p = positions.value[k]
        if (p.assigned && Math.hypot(p.x - x, p.y - y) <= 24) {
            dragging = true
            dragKey = k
            hoveredKey.value = k
            dragOffsetX = x - p.x
            dragOffsetY = y - p.y
            canvasStatus.value = `Moviendo a ${p.assigned.name}.`
            return
        }
    }
}

function onDrag(e) {
    if (!dragging || !dragKey) return
    // No permitir mover al portero si está incluido
    if (props.includeGoalkeeper && dragKey === 'GK') return

    const { x, y } = toCanvasPoint(e)
    positions.value[dragKey].x = x - dragOffsetX
    positions.value[dragKey].y = y - dragOffsetY
    drawField()
}

function releaseAssignedPlayer(posKey, statusMessage = null) {
    const prev = positions.value[posKey]?.assigned
    if (!prev) return false

    positions.value[posKey].assigned = null
    emits('unassign-player', { player: prev, posKey })
    emits('update-positions', getPositionsSnapshot())

    if (statusMessage) {
        canvasStatus.value = statusMessage
    }

    drawField()
    return true
}

function endDrag(e = null) {
    if (dragging && dragKey) {
        const droppedInPlayerList = e?.clientX != null && e?.clientY != null
            ? document.elementFromPoint(e.clientX, e.clientY)?.closest('.player-panel, .player-list')
            : null

        if (droppedInPlayerList) {
            const playerName = positions.value[dragKey].assigned?.name
            releaseAssignedPlayer(dragKey, `${playerName} regresó a la plantilla disponible.`)
            dragging = false
            dragKey = null
            hoveredKey.value = null
            return
        }

        // Actualizar el rol específico de la posición movida
        positions.value[dragKey].specificRole = getSpecificRole(positions.value[dragKey]);
        const playerName = positions.value[dragKey].assigned?.name
        emits('update-positions', getPositionsSnapshot())
        if (playerName) {
            canvasStatus.value = `${playerName} reubicado en ${positions.value[dragKey].specificRole}.`
        }
    }
    dragging = false
    dragKey = null
}

function onCanvasPointerDown(e) {
    if (!canvas.value) return
    canvas.value.setPointerCapture?.(e.pointerId)
    startDrag(e)
}

function onCanvasPointerMove(e) {
    if (!canvas.value) return
    const { x, y } = toCanvasPoint(e)

    if (dragging) {
        onDrag(e)
        return
    }

    const nearest = findNearestPosition(x, y, 36)
    hoveredKey.value = nearest

    if (nearest && positions.value[nearest]?.assigned) {
        const position = positions.value[nearest]
        canvasStatus.value = `${position.assigned.name} | ${position.specificRole || nearest}`
    } else if (nearest) {
        canvasStatus.value = `${positions.value[nearest]?.specificRole || nearest} disponible para asignación.`
    } else {
        canvasStatus.value = 'Arrastra jugadores al campo o mueve un titular dentro de la cancha.'
    }

    drawField()
}

function onCanvasPointerUp(e) {
    if (dragging) {
        endDrag(e)
    }
    canvas.value?.releasePointerCapture?.(e.pointerId)
}

// doble click para desasignar
function onDblClick(e) {
    const { x, y } = toCanvasPoint(e)

    // drawDebugPoint(x, y, 'red')

    // Radio más pequeño para doble click para mayor precisión
    const nearest = findNearestPosition(x, y, 50)

    if (!nearest) return

    const prev = positions.value[nearest].assigned
    if (prev) releaseAssignedPlayer(nearest, `${prev.name} regresó a la lista de disponibles.`)
}

function drawDebugPoint(x, y, color = 'red') {
    if (!ctx) return
    ctx.save()
    ctx.fillStyle = color
    ctx.beginPath()
    ctx.arc(x, y, 5, 0, Math.PI * 2)
    ctx.fill()
    ctx.restore()
}

function getMarkerPalette(type) {
    switch (type) {
        case 'defense':
            return {
                fill: '#8fd3ff',
                stroke: '#0c5a82',
                glow: 'rgba(90, 193, 255, 0.38)'
            }
        case 'mid-defensive':
        case 'mid-offensive':
        case 'midfield':
            return {
                fill: '#f7df7c',
                stroke: '#9b7525',
                glow: 'rgba(247, 223, 124, 0.35)'
            }
        case 'attack':
            return {
                fill: '#ff8f8f',
                stroke: '#9b1f2d',
                glow: 'rgba(255, 120, 120, 0.35)'
            }
        default:
            return {
                fill: '#d8dde5',
                stroke: '#576475',
                glow: 'rgba(129, 145, 166, 0.25)'
            }
    }
}

function drawBackgroundOverlay() {
    ctx.save()

    const topShade = ctx.createLinearGradient(0, 0, 0, canvasHeight.value * 0.25)
    topShade.addColorStop(0, 'rgba(0, 0, 0, 0.22)')
    topShade.addColorStop(1, 'rgba(0, 0, 0, 0)')
    ctx.fillStyle = topShade
    ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value * 0.25)

    const bottomShade = ctx.createLinearGradient(0, canvasHeight.value, 0, canvasHeight.value * 0.76)
    bottomShade.addColorStop(0, 'rgba(0, 0, 0, 0.26)')
    bottomShade.addColorStop(1, 'rgba(0, 0, 0, 0)')
    ctx.fillStyle = bottomShade
    ctx.fillRect(0, canvasHeight.value * 0.76, canvasWidth.value, canvasHeight.value * 0.24)

    ctx.restore()
}

function drawPositionNode(p, palette, isHovered) {
    const radius = isHovered ? 34 : 30

    ctx.save()
    ctx.shadowColor = palette.glow
    ctx.shadowBlur = isHovered ? 26 : 16
    ctx.shadowOffsetX = 0
    ctx.shadowOffsetY = 8

    const gradient = ctx.createRadialGradient(p.x - 8, p.y - 10, 8, p.x, p.y, radius)
    gradient.addColorStop(0, '#ffffff')
    gradient.addColorStop(0.2, palette.fill)
    gradient.addColorStop(1, palette.stroke)

    ctx.beginPath()
    ctx.fillStyle = gradient
    ctx.strokeStyle = '#ffffff'
    ctx.lineWidth = isHovered ? 3 : 2
    ctx.arc(p.x, p.y, radius, 0, Math.PI * 2)
    ctx.fill()
    ctx.stroke()
    ctx.restore()

    if (isHovered) {
        ctx.save()
        ctx.beginPath()
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.75)'
        ctx.lineWidth = 2
        ctx.arc(p.x, p.y, radius + 7, 0, Math.PI * 2)
        ctx.stroke()
        ctx.restore()
    }
}

// dibujo del campo y jugadores
function drawField() {
    if (!canvas.value) return
    if (!ctx) ctx = canvas.value.getContext('2d')
    ctx.imageSmoothingEnabled = true
    ctx.clearRect(0, 0, canvasWidth.value, canvasHeight.value)

    if (fieldImg.complete) {
        // Dibujar la imagen escalada al tamaño del canvas
        ctx.drawImage(fieldImg, 0, 0, canvasWidth.value, canvasHeight.value)
    } else {
        ctx.fillStyle = '#47a447'
        ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value)
    }

    drawBackgroundOverlay()

    for (const k of posKeys.value) {
        const p = positions.value[k]
        const palette = getMarkerPalette(p.type)
        const isHovered = hoveredKey.value === k || dragKey === k

        drawPositionNode(p, palette, isHovered)

        // Jugador o placeholder
        if (p.assigned) {
            // VERIFICAR SI imgObj ES UNA IMAGEN VÁLIDA
            if (p.assigned.imgObj && p.assigned.imgObj instanceof Image && p.assigned.imgObj.complete) {
                try {
                    // Crear un círculo de recorte para la imagen
                    ctx.save()
                    ctx.beginPath()
                    ctx.arc(p.x, p.y, 25, 0, Math.PI * 2)
                    ctx.closePath()
                    ctx.clip()

                    // Dibujar la imagen dentro del círculo de recorte
                    ctx.drawImage(p.assigned.imgObj, p.x - 25, p.y - 25, 50, 50)
                    ctx.restore()
                } catch (error) {
                    console.error('Error dibujando imagen:', error)
                    // Fallback: dibujar marcador por defecto
                    drawDefaultMarker(p.x, p.y, p.assigned.name, palette)
                }
            } else if (p.assigned.img) {
                // Si hay URL de imagen pero no está cargada, cargarla
                if (!p.assigned.imgLoading) { // Evitar múltiples cargas
                    p.assigned.imgLoading = true
                    const img = new Image()
                    img.onload = () => {
                        p.assigned.imgObj = img
                        p.assigned.imgLoading = false
                        drawField() // Redibujar cuando la imagen se cargue
                    }
                    img.onerror = () => {
                        console.error('Error cargando imagen:', p.assigned.img)
                        p.assigned.imgLoading = false
                        p.assigned.imgObj = null
                        drawField()
                    }
                    img.src = p.assigned.img
                }
                // Mientras carga, dibujar marcador temporal
                drawDefaultMarker(p.x, p.y, p.assigned.name, palette)
            } else {
                // Sin imagen, usar marcador por defecto
                drawDefaultMarker(p.x, p.y, p.assigned.name, palette)
            }

            // Dibujar nombre en todos los casos
            drawPlayerName(p.x, p.y, p.assigned.name, p.specificRole);
        } else {
            // Marcador por defecto con clave corta
            drawDefaultMarker(p.x, p.y, p.specificRole || k, palette)
        }
    }
}

function drawDefaultMarker(x, y, label, palette) {
    ctx.fillStyle = '#0b1d17'
    ctx.font = '700 13px "Trebuchet MS", Arial, sans-serif'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'

    // Acortar label más agresivamente para evitar desbordamiento
    let shortLabel = label
    if (label.length > 10) {
        shortLabel = label.substring(0, 8) + '…'
    }
    ctx.fillText(shortLabel, x, y)
}

function drawPlayerName(x, y, name, specificRole = null) {
    const shortText = name.length > 14 ? name.substring(0, 12) + '…' : name
    const roleText = specificRole && specificRole !== 'Jugador' ? specificRole : ''
    ctx.font = '700 13px "Trebuchet MS", Arial, sans-serif'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'

    const textWidth = ctx.measureText(shortText).width
    const roleWidth = roleText ? ctx.measureText(roleText).width : 0
    const padding = 12
    const totalWidth = Math.max(textWidth + padding * 2, roleText ? roleWidth + 24 : 90)
    const totalHeight = roleText ? 42 : 28

    // Posicionar en la parte INFERIOR centrado
    const badgeX = x - totalWidth / 2
    const badgeY = y + 38

    ctx.save()
    ctx.shadowColor = 'rgba(0, 0, 0, 0.28)'
    ctx.shadowBlur = 10
    ctx.shadowOffsetX = 0
    ctx.shadowOffsetY = 4
    const gradient = ctx.createLinearGradient(badgeX, badgeY, badgeX, badgeY + totalHeight)
    gradient.addColorStop(0, 'rgba(12, 28, 22, 0.9)')
    gradient.addColorStop(1, 'rgba(7, 18, 14, 0.94)')

    roundRect(ctx, badgeX, badgeY, totalWidth, totalHeight, 15)
    ctx.fillStyle = gradient
    ctx.fill()
    ctx.restore()

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)'
    ctx.lineWidth = 1.6
    roundRect(ctx, badgeX, badgeY, totalWidth, totalHeight, 15)
    ctx.stroke()

    ctx.fillStyle = 'white'
    ctx.font = '700 13px "Trebuchet MS", Arial, sans-serif'
    ctx.fillText(shortText, x, badgeY + (roleText ? 13 : totalHeight / 2))

    if (roleText) {
        ctx.fillStyle = 'rgba(194, 231, 212, 0.9)'
        ctx.font = '600 10px "Trebuchet MS", Arial, sans-serif'
        ctx.fillText(roleText, x, badgeY + 29)
    }

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)'
    ctx.lineWidth = 1.5
    ctx.beginPath()
    ctx.moveTo(x, y + 30)
    ctx.lineTo(x, badgeY)
    ctx.stroke()
}

// Función auxiliar mejorada para bordes redondeados
function roundRect(ctx, x, y, width, height, radius) {
    ctx.beginPath()
    ctx.moveTo(x + radius, y)
    ctx.lineTo(x + width - radius, y)
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius)
    ctx.lineTo(x + width, y + height - radius)
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height)
    ctx.lineTo(x + radius, y + height)
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius)
    ctx.lineTo(x, y + radius)
    ctx.quadraticCurveTo(x, y, x + radius, y)
    ctx.closePath()
}

// export/import layout
function getPositionsSnapshot() {
    const snap = {}
    for (const k of posKeys.value) {
        snap[k] = {
            x: positions.value[k].x,
            y: positions.value[k].y,
            type: positions.value[k].type || 'midfield',
            line: positions.value[k].line || 0,
            order: positions.value[k].order || 0,
            key: positions.value[k].key,
            specificRole: positions.value[k].specificRole || getSpecificRole(positions.value[k]),
            assigned: positions.value[k].assigned ? {
                id: positions.value[k].assigned.id,
                name: positions.value[k].assigned.name,
                number: positions.value[k].assigned.number,
                img: positions.value[k].assigned.img
            } : null
        }
    }
    return {
        formation: props.formation,
        playerCount: props.playerCount,
        includeGoalkeeper: props.includeGoalkeeper,
        canvasWidth: canvasWidth.value,
        canvasHeight: canvasHeight.value,
        positions: snap
    }
}

// reset default
function resetToDefault() {
    let hadAssignedPlayers = false

    for (const k of posKeys.value) {
        if (positions.value[k]?.assigned) {
            hadAssignedPlayers = true
            positions.value[k].assigned = null
        }
    }

    if (hadAssignedPlayers) {
        emits('reset-lineup')
    }

    applyFormation(props.formation)
    hoveredKey.value = null
    canvasStatus.value = 'Cancha reiniciada a la distribución base.'
    drawField()
}

// Función para extraer el canvas como imagen
function getCanvasImage(format = 'png', quality = 0.9) {
    if (!canvas.value) return null;

    const exportCanvas = buildExportCanvas()
    if (!exportCanvas) return null;

    // Convertir canvas a imagen base64
    const dataUrl = exportCanvas.toDataURL(`image/${format}`, quality);
    return {
        base64: dataUrl,
        format: format,
        width: exportCanvas.width,
        height: exportCanvas.height
    };
}

function buildExportCanvas() {
    if (!canvas.value) return null

    const roster = [...props.availablePlayers]
        .filter(player => player?.name)
        .sort((left, right) => left.name.localeCompare(right.name, 'es', { sensitivity: 'base' }))

    const width = canvasWidth.value
    const baseHeight = canvasHeight.value
    const sectionGap = 24
    const paddingX = 42
    const paddingY = 32
    const columnGap = 20
    const columns = roster.length > 8 ? 3 : 2
    const cardHeight = 34
    const rowGap = 12
    const headerHeight = 56
    const emptyHeight = 56
    const rows = Math.max(Math.ceil(roster.length / columns), 1)
    const rosterHeight = headerHeight + paddingY + (roster.length ? rows * cardHeight + (rows - 1) * rowGap : emptyHeight)
    const exportCanvas = document.createElement('canvas')
    exportCanvas.width = width
    exportCanvas.height = baseHeight + sectionGap + rosterHeight

    const exportCtx = exportCanvas.getContext('2d')
    exportCtx.imageSmoothingEnabled = true
    exportCtx.drawImage(canvas.value, 0, 0)

    const sectionY = baseHeight + sectionGap
    const sectionHeight = exportCanvas.height - sectionY
    const panelGradient = exportCtx.createLinearGradient(0, sectionY, 0, exportCanvas.height)
    panelGradient.addColorStop(0, '#0d1f18')
    panelGradient.addColorStop(1, '#10281f')
    exportCtx.fillStyle = panelGradient
    exportCtx.fillRect(0, sectionY, width, sectionHeight)

    exportCtx.strokeStyle = 'rgba(255, 255, 255, 0.12)'
    exportCtx.lineWidth = 1
    exportCtx.beginPath()
    exportCtx.moveTo(paddingX, sectionY)
    exportCtx.lineTo(width - paddingX, sectionY)
    exportCtx.stroke()

    exportCtx.fillStyle = 'rgba(194, 231, 212, 0.85)'
    exportCtx.font = '800 24px "Trebuchet MS", Arial, sans-serif'
    exportCtx.textAlign = 'left'
    exportCtx.textBaseline = 'alphabetic'
    exportCtx.fillText('Convocatoria', paddingX, sectionY + 36)

    if (!roster.length) {
        drawExportEmptyState(exportCtx, {
            x: paddingX,
            y: sectionY + headerHeight,
            width: width - paddingX * 2,
            height: emptyHeight
        })
        return exportCanvas
    }

    const cardWidth = (width - paddingX * 2 - columnGap * (columns - 1)) / columns
    roster.forEach((player, index) => {
        const column = index % columns
        const row = Math.floor(index / columns)
        const x = paddingX + column * (cardWidth + columnGap)
        const y = sectionY + headerHeight + row * (cardHeight + rowGap)

        drawExportRosterCard(exportCtx, {
            x,
            y,
            width: cardWidth,
            height: cardHeight,
            name: player.name
        })
    })

    return exportCanvas
}

function drawExportEmptyState(exportCtx, box) {
    exportCtx.fillStyle = 'rgba(255, 255, 255, 0.06)'
    roundRect(exportCtx, box.x, box.y, box.width, box.height, 16)
    exportCtx.fill()

    exportCtx.strokeStyle = 'rgba(255, 255, 255, 0.12)'
    exportCtx.lineWidth = 1
    roundRect(exportCtx, box.x, box.y, box.width, box.height, 16)
    exportCtx.stroke()

    exportCtx.fillStyle = 'rgba(235, 243, 239, 0.92)'
    exportCtx.font = '600 15px "Trebuchet MS", Arial, sans-serif'
    exportCtx.textAlign = 'center'
    exportCtx.fillText('No hay jugadores disponibles fuera de la cancha.', box.x + box.width / 2, box.y + 36)
}

function drawExportRosterCard(exportCtx, card) {
    exportCtx.fillStyle = 'rgba(255, 255, 255, 0.06)'
    roundRect(exportCtx, card.x, card.y, card.width, card.height, 14)
    exportCtx.fill()

    exportCtx.strokeStyle = 'rgba(255, 255, 255, 0.12)'
    exportCtx.lineWidth = 1
    roundRect(exportCtx, card.x, card.y, card.width, card.height, 14)
    exportCtx.stroke()

    exportCtx.fillStyle = '#ffffff'
    exportCtx.font = '700 14px "Trebuchet MS", Arial, sans-serif'
    const nameX = card.x + 14
    const maxNameWidth = card.width - 28
    exportCtx.fillText(truncateText(exportCtx, card.name, maxNameWidth), nameX, card.y + 22)
}

function truncateText(exportCtx, text, maxWidth) {
    if (!text) return ''
    if (exportCtx.measureText(text).width <= maxWidth) return text

    let truncated = text
    while (truncated.length > 0 && exportCtx.measureText(`${truncated}…`).width > maxWidth) {
        truncated = truncated.slice(0, -1)
    }

    return `${truncated}…`
}

// Función para guardar solo la imagen
function saveCanvasImage() {
    const canvasImage = getCanvasImage();
    if (!canvasImage) return;

    const a = document.createElement('a');
    a.href = canvasImage.base64;
    a.download = `formacion_${props.formation}_${new Date().toISOString().slice(0,10)}.png`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    canvasStatus.value = 'Imagen PNG descargada con convocatoria.'
}

// Exponer funciones a padre
defineExpose({
    applyFormation,
    resetToDefault,
    saveCanvasImage,
    getCanvasImage
})

// watchers
watch(() => props.formation, (n) => {
    applyFormation(n)
})

// Watcher para cuando cambie el número de jugadores
watch(() => props.playerCount, () => {
    initializePositions()
    applyFormation(props.formation)
})

// mounted / hooks
onMounted(() => {
    if (canvas.value) {
        ctx = canvas.value.getContext('2d')
        if (fieldImg.complete) {
            canvas.value.width = fieldImg.naturalWidth
            canvas.value.height = fieldImg.naturalHeight
            canvasWidth.value = fieldImg.naturalWidth
            canvasHeight.value = fieldImg.naturalHeight
            applyFormation(props.formation)
            drawField()
        }
        canvas.value.addEventListener('dragover', (ev) => ev.preventDefault())
        canvas.value.addEventListener('drop', onDrop)
    }
})

onBeforeUnmount(() => {
    if (canvas.value) {
        canvas.value.removeEventListener('drop', onDrop)
    }
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
