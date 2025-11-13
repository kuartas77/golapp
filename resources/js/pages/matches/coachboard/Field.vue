<template>
    <div>
    <button @click="exportLayout">Guardar</button>
        <div class="field-wrapper position-relative">
            <canvas ref="canvas" @dblclick="onDblClick" @dragover.prevent @drop.prevent="onDrop"></canvas>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'

const props = defineProps({
    formation: { type: String, required: true },
    formationsMap: { type: Object, required: true },
    playersField: { type: Array, required: true },
    playerCount: { type: Number, default: 11 }, // Nuevo prop para número de jugadores
    includeGoalkeeper: { type: Boolean, default: true } // Si incluye portero o no
})

const emits = defineEmits(['assign-player', 'unassign-player', 'update-positions', 'layout-saved', 'layout-loaded'])

const canvas = ref(null)
let ctx = null
const fieldImg = new Image()
fieldImg.src = '/img/field-vertical.webp'

const canvasWidth = ref(0)
const canvasHeight = ref(0)

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

    // Radio adaptativo basado en la posición Y
    const adaptiveRadius = radius * (0.7 + 0.3 * (y / canvasHeight.value))

    for (const k of posKeys.value) {
        const p = positions.value[k]
        // Si la posición ya tiene un jugador, hacerla menos "atractiva" para evitar reemplazos accidentales
        const penalty = p.assigned ? 15 : 0

        const dx = p.x - x
        const dy = p.y - y
        const d = Math.sqrt(dx * dx + dy * dy) + penalty

        if (d < bestDist && d <= adaptiveRadius) {
            bestDist = d
            best = k
        }
    }
    return best
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

    const clone = {
        ...playerObj,
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
        const rect = canvas.value.getBoundingClientRect()
        const scaleX = canvasWidth.value / rect.width
        const scaleY = canvasHeight.value / rect.height
        const x = (e.clientX - rect.left) * scaleX
        const y = (e.clientY - rect.top) * scaleY

        // Encontrar la posición más cercana con radio más pequeño para mayor precisión
        const nearest = findNearestPosition(x, y, 40) // Reducir el radio para más precisión

        if (nearest) {
            assignPlayerToPos(player, nearest)
        }
    } catch (error) {
        console.error('Error processing drop:', error)
    }
}

// DnD interno: mover jugador en el canvas
function startDrag(e) {
    const rect = canvas.value.getBoundingClientRect()
    const scaleX = canvasWidth.value / rect.width
    const scaleY = canvasHeight.value / rect.height
    const x = (e.clientX - rect.left) * scaleX
    const y = (e.clientY - rect.top) * scaleY

    for (const k of posKeys.value) {
        // No permitir arrastrar portero si está incluido
        if (props.includeGoalkeeper && k === 'GK') continue

        const p = positions.value[k]
        if (p.assigned && Math.hypot(p.x - x, p.y - y) <= 24) {
            dragging = true
            dragKey = k
            dragOffsetX = x - p.x
            dragOffsetY = y - p.y
            return
        }
    }
}

function onDrag(e) {
    if (!dragging || !dragKey) return
    // No permitir mover al portero si está incluido
    if (props.includeGoalkeeper && dragKey === 'GK') return

    const rect = canvas.value.getBoundingClientRect()
    const scaleX = canvasWidth.value / rect.width
    const scaleY = canvasHeight.value / rect.height
    const x = (e.clientX - rect.left) * scaleX
    const y = (e.clientY - rect.top) * scaleY
    positions.value[dragKey].x = x - dragOffsetX
    positions.value[dragKey].y = y - dragOffsetY
    drawField()
}

function endDrag() {
    if (dragging && dragKey) {
        // Actualizar el rol específico de la posición movida
        positions.value[dragKey].specificRole = getSpecificRole(positions.value[dragKey]);
        emits('update-positions', getPositionsSnapshot())
    }
    dragging = false
    dragKey = null
}

// doble click para desasignar
function onDblClick(e) {
    const rect = canvas.value.getBoundingClientRect()
    const scaleX = canvasWidth.value / rect.width
    const scaleY = canvasHeight.value / rect.height
    const x = (e.clientX - rect.left) * scaleX
    const y = (e.clientY - rect.top) * scaleY

    // Radio más pequeño para doble click para mayor precisión
    const nearest = findNearestPosition(x, y, 25)

    if (!nearest) return

    const prev = positions.value[nearest].assigned
    if (prev) {
        positions.value[nearest].assigned = null
        emits('unassign-player', { player: prev, posKey: nearest })
        drawField()
    }
}

// dibujo del campo y jugadores
function drawField() {
    if (!canvas.value) return
    if (!ctx) ctx = canvas.value.getContext('2d')
    ctx.clearRect(0, 0, canvasWidth.value, canvasHeight.value)

    if (fieldImg.complete) {
        // Dibujar la imagen escalada al tamaño del canvas
        ctx.drawImage(fieldImg, 0, 0, canvasWidth.value, canvasHeight.value)
    } else {
        ctx.fillStyle = '#47a447'
        ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value)
    }

    for (const k of posKeys.value) {
        const p = positions.value[k]

        // Color del marcador basado en el tipo de posición
        let circleColor = '#cccccc'
        let strokeColor = '#666666'

        switch (p.type) {
            case 'defense':
                circleColor = '#a8d5e5' // Azul claro para defensas
                strokeColor = '#2c6f8a'
                break;
            case 'mid-defensive':
            case 'mid-offensive':
            case 'midfield':
                circleColor = '#f0e6a8' // Amarillo claro para medios
                strokeColor = '#b3a44e'
                break;
            case 'attack':
                circleColor = '#f5a8a8' // Rojo claro para delanteros
                strokeColor = '#a84e4e'
                break;
        }

        // Círculo base con color según el tipo
        ctx.beginPath()
        ctx.fillStyle = circleColor
        ctx.strokeStyle = strokeColor
        ctx.lineWidth = 2
        ctx.arc(p.x, p.y, 30, 0, Math.PI * 2)
        ctx.fill()
        ctx.stroke()

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
                    drawDefaultMarker(p.x, p.y, p.assigned.name, circleColor, strokeColor)
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
                drawDefaultMarker(p.x, p.y, p.assigned.name, circleColor, strokeColor)
            } else {
                // Sin imagen, usar marcador por defecto
                drawDefaultMarker(p.x, p.y, p.assigned.name, circleColor, strokeColor)
            }

            // Dibujar nombre en todos los casos
            drawPlayerName(p.x, p.y, p.assigned.name);
        } else {
            // Marcador por defecto con clave corta
            drawDefaultMarker(p.x, p.y, k, circleColor, strokeColor)
        }
    }
}

function drawDefaultMarker(x, y, label, circleColor = '#cccccc', strokeColor = '#666666') {
    ctx.beginPath()
    ctx.fillStyle = circleColor
    ctx.strokeStyle = strokeColor
    ctx.lineWidth = 2
    ctx.arc(x, y, 30, 0, Math.PI * 2)
    ctx.fill()
    ctx.stroke()
    ctx.fillStyle = '#222'
    ctx.font = 'bold 16px sans-serif' // Reducir tamaño de fuente
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'

    // Acortar label más agresivamente para evitar desbordamiento
    let shortLabel = label
    if (label.length > 6) {
        shortLabel = label.substring(0, 4) + '...'
    }
    ctx.fillText(shortLabel, x, y)
}

function drawPlayerName(x, y, name, specificRole = null) {
    const shortText = name.length > 12 ? name.substring(0, 10) + '...' : name
    ctx.font = 'bold 13px "Arial Black", Arial, sans-serif'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'

    const textWidth = ctx.measureText(shortText).width
    const padding = 12
    const totalWidth = Math.max(textWidth + padding * 2, 80) // Ancho mínimo
    const totalHeight = 26

    // Posicionar en la parte INFERIOR centrado
    const badgeX = x - totalWidth / 2
    const badgeY = y + 38

    // Efectos de sombra y gradiente
    ctx.save()

    // Sombra exterior
    ctx.shadowColor = 'rgba(0, 0, 0, 0.4)'
    ctx.shadowBlur = 6
    ctx.shadowOffsetX = 0
    ctx.shadowOffsetY = 2

    // Gradiente principal del badge
    const gradient = ctx.createLinearGradient(badgeX, badgeY, badgeX, badgeY + totalHeight)
    gradient.addColorStop(0, '#e74c3c')
    gradient.addColorStop(0.4, '#c0392b')
    gradient.addColorStop(0.6, '#a53125')
    gradient.addColorStop(1, '#e74c3c')

    // Badge con bordes redondeados
    roundRect(ctx, badgeX, badgeY, totalWidth, totalHeight, 15)
    ctx.fillStyle = gradient
    ctx.fill()

    // Resaltar borde superior
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.4)'
    ctx.lineWidth = 1
    ctx.beginPath()
    ctx.moveTo(badgeX + 5, badgeY + 1)
    ctx.lineTo(badgeX + totalWidth - 5, badgeY + 1)
    ctx.stroke()

    ctx.restore()

    // Borde exterior blanco
    ctx.strokeStyle = '#fff'
    ctx.lineWidth = 2
    roundRect(ctx, badgeX, badgeY, totalWidth, totalHeight, 15)
    ctx.stroke()

    // Efecto de costura o patrón deportivo (opcional)
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)'
    ctx.lineWidth = 1
    ctx.setLineDash([1, 2])
    ctx.beginPath()
    ctx.moveTo(badgeX + 8, badgeY + totalHeight / 2)
    ctx.lineTo(badgeX + totalWidth - 8, badgeY + totalHeight / 2)
    ctx.stroke()
    ctx.setLineDash([])

    // Texto con efecto de relieve
    ctx.save()
    ctx.shadowColor = 'rgba(0, 0, 0, 0.7)'
    ctx.shadowBlur = 3
    ctx.shadowOffsetX = 1
    ctx.shadowOffsetY = 1

    ctx.fillStyle = 'white'
    ctx.fillText(shortText, x, badgeY + totalHeight / 2 + 1)
    ctx.restore()

    // Conector sólido en lugar de punteado
    ctx.strokeStyle = 'rgba(231, 76, 60, 0.8)'
    ctx.lineWidth = 2
    ctx.beginPath()
    ctx.moveTo(x, y + 30)
    ctx.lineTo(x, badgeY)
    ctx.stroke()

    // Punto de conexión en el marcador
    ctx.fillStyle = '#e74c3c'
    ctx.beginPath()
    ctx.arc(x, y + 30, 3, 0, Math.PI * 2)
    ctx.fill()

    // Punto de conexión en el badge
    ctx.beginPath()
    ctx.arc(x, badgeY, 3, 0, Math.PI * 2)
    ctx.fill()
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

function exportLayout() {
    // const data = JSON.stringify(getPositionsSnapshot(), null, 2)
    // console.log(data)
    // const blob = new Blob([data], { type: 'application/json' })
    // const url = URL.createObjectURL(blob)
    // const a = document.createElement('a')
    // a.href = url
    // a.download = 'layout.json'
    // a.click()
    // URL.revokeObjectURL(url)
    emits('layout-saved', getPositionsSnapshot())
}

function importLayoutFile(e) {
    const file = e.target.files[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = () => {
        try {
            const obj = JSON.parse(reader.result)
            loadLayout(obj)
            emits('layout-loaded', obj)
        } catch (err) {
            console.error('JSON inválido', err)
        }
    }
    reader.readAsText(file)
}

function loadLayout(obj) {
    if (!obj || !obj.positions) return

    if (obj.canvasWidth && obj.canvasHeight) {
        canvasWidth.value = obj.canvasWidth
        canvasHeight.value = obj.canvasHeight
        canvas.value.width = canvasWidth.value
        canvas.value.height = canvasHeight.value
    }

    // Primero, limpiar todas las asignaciones existentes
    for (const k of posKeys.value) {
        positions.value[k].assigned = null
    }

    // Luego, asignar los jugadores del layout
    for (const k in obj.positions) {
        if (positions.value[k]) {
            const s = obj.positions[k]
            positions.value[k] = {
                ...positions.value[k],
                x: s.x,
                y: s.y,
                type: s.type || 'midfield',
                line: s.line || 0,
                order: s.order || 0,
                key: s.key || k,
                specificRole: s.specificRole || getSpecificRole({...s, key: k})
            }

            if (s.assigned) {
                // Buscar el jugador en la lista actual de jugadores
                const p = props.playersField.find(pp => pp.id === s.assigned.id)
                if (p) {
                    positions.value[k].assigned = { ...p  }
                    if (p.img) {
                        const img = new Image()
                        img.src = p.img
                        img.onload = () => {
                            positions.value[k].assigned.imgObj = img
                            drawField()
                        }
                    }
                } else {
                    // Si no se encuentra en la lista actual, usar los datos del layout
                    positions.value[k].assigned = { ...s.assigned, imgObj: null }
                }
            }
        }
    }

    // Actualizar roles por si acaso
    updateSpecificRoles();

    drawField()
}

// reset default
function resetToDefault() {
    for (const k of posKeys.value) positions.value[k].assigned = null
    applyFormation(props.formation)
    drawField()
}

// Exponer funciones a padre
defineExpose({ exportLayout, loadLayout, applyFormation })

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
.field-wrapper {
    width: 100%;
    max-width: 800px;
    /* Limitar el ancho máximo */
    margin: 0 auto;
    /* Centrar */
    /* Quitamos el padding-bottom y la posición relativa */
    position: relative;
}

canvas {
    display: block;
    width: 100%;
    height: auto;
    touch-action: none;
    -webkit-tap-highlight-color: transparent;
    /* Quitamos la posición absoluta */
}

/* Para pantallas muy pequeñas */
@media (max-width: 480px) {
    .field-wrapper {
        padding-bottom: 85%;
        /* Un poco más alto en móviles */
    }
}
</style>