import { computed, onMounted, ref, watch } from 'vue'

/**
 * Centraliza la lógica táctica del coachboard.
 *
 * Esta versión mantiene el contrato público del componente, pero usa SVG para
 * la superficie interactiva. El canvas queda como un detalle interno solo para
 * la exportación PNG, lo que nos permite probar una implementación declarativa
 * sin cambiar el flujo del padre.
 */
export default function useCoachBoardField(props, emit) {
    const PLAYER_NODE_RADIUS = 36
    const PLAYER_NODE_HOVER_RADIUS = 40
    const PLAYER_IMAGE_RADIUS = 31
    const PLAYER_LABEL_OFFSET_Y = 44
    const DEFAULT_FIELD_WIDTH = 900
    const DEFAULT_FIELD_HEIGHT = 1400
    const DEFAULT_STATUS = 'Arrastra jugadores al campo o mueve un titular dentro de la cancha.'

    const canvas = ref(null)
    const canvasWidth = ref(DEFAULT_FIELD_WIDTH)
    const canvasHeight = ref(DEFAULT_FIELD_HEIGHT)
    const hoveredKey = ref(null)
    const canvasStatus = ref(DEFAULT_STATUS)
    const positions = ref({})

    const isDragging = ref(false)
    const dragKey = ref(null)
    const clipPathPrefix = `coachboard-slot-${Math.random().toString(36).slice(2, 8)}`

    let dragPointerId = null
    let dragOffsetX = 0
    let dragOffsetY = 0
    let suppressNextClick = false
    let lastSelectionHint = ''

    const fieldImg = new Image()
    fieldImg.src = '/img/field-vertical.webp'

    const assignedCount = computed(() =>
        posKeys.value.reduce((total, key) => total + (positions.value[key]?.assigned ? 1 : 0), 0)
    )

    const openSlots = computed(() => Math.max(props.playerCount - assignedCount.value, 0))

    const fieldViewBox = computed(() => `0 0 ${canvasWidth.value} ${canvasHeight.value}`)

    /**
     * Genera las claves tácticas disponibles en la cancha.
     * `GK` se reserva para el portero y el resto se construye como `P1..Pn`.
     */
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

    const renderedPositions = computed(() =>
        posKeys.value.map((key) => {
            const position = positions.value[key] || { x: 0, y: 0, assigned: null, key }
            const palette = getMarkerPalette(position.type)
            const shortName = position.assigned?.name ? truncateDisplayText(position.assigned.name, 14, 12) : ''
            const roleText = position.specificRole && position.specificRole !== 'Jugador' ? position.specificRole : ''
            const badgeWidth = estimateBadgeWidth(shortName, roleText)
            const badgeHeight = roleText ? 42 : 28

            return {
                ...position,
                key,
                palette,
                isHovered: hoveredKey.value === key || dragKey.value === key,
                clipId: `${clipPathPrefix}-${key}`,
                fallbackLabel: truncateDisplayText(position.specificRole || key, 10, 8),
                playerShortName: shortName,
                roleText,
                badgeWidth,
                badgeHeight,
                badgeX: (position.x || 0) - badgeWidth / 2,
                badgeY: (position.y || 0) + PLAYER_LABEL_OFFSET_Y
            }
        })
    )

    watch(posKeys, () => {
        initializePositions()
    }, { immediate: true })

    fieldImg.onload = () => {
        canvasWidth.value = fieldImg.naturalWidth || DEFAULT_FIELD_WIDTH
        canvasHeight.value = fieldImg.naturalHeight || DEFAULT_FIELD_HEIGHT
        initializePositions()
        applyFormation(props.formation)
    }

    /* --------------------------------------------------------------------- */
    /* Construcción táctica de posiciones                                    */
    /* --------------------------------------------------------------------- */
    function initializePositions() {
        const newPositions = {}
        posKeys.value.forEach((key) => {
            newPositions[key] = positions.value[key] || { x: 0, y: 0, assigned: null, key }
        })
        positions.value = newPositions
    }

    function parseFormationString(str) {
        if (!str) return null

        const parts = str.split('-').map(segment => Number(segment))
        if (parts.some(number => Number.isNaN(number) || number < 0)) {
            return null
        }

        return parts
    }

    /**
     * Traduce una posición técnica a un rol legible para el usuario y para el
     * payload que luego se guarda en el partido.
     */
    function getSpecificRole(position) {
        const { type, line, x } = position
        const canvasCenterX = canvasWidth.value / 2

        if (position.key === 'GK') return 'Portero'

        if (type === 'defense') {
            if (line === 0) {
                const isLeft = x < canvasCenterX - 50
                const isRight = x > canvasCenterX + 50
                const isCenter = !isLeft && !isRight

                if (isCenter) return 'Defensa(Central)'
                if (isLeft) return 'Defensa(Izquierdo)'
                if (isRight) return 'Defensa(Derecho)'
            }

            return 'Defensa'
        }

        if (type?.includes('mid')) {
            const isWide = Math.abs(x - canvasCenterX) > canvasWidth.value * 0.25
            const isLeftWide = x < canvasCenterX - 100
            const isRightWide = x > canvasCenterX + 100

            if (type === 'mid-defensive') {
                if (isWide) {
                    return isLeftWide ? 'Volante(Defensivo Izquierdo)' : 'Volante(Defensivo Derecho)'
                }
                return 'Volante(Defensivo Central)'
            }

            if (type === 'mid-offensive') {
                if (isWide) {
                    return isLeftWide ? 'Volante(Ofensivo Izquierdo)' : 'Volante(Ofensivo Derecho)'
                }
                return 'Volante(Ofensivo Central)'
            }

            if (isWide) {
                if (isLeftWide) return 'Volante(Extremo Izquierdo)'
                if (isRightWide) return 'Volante(Extremo Derecho)'
            }

            if (line === 1) return 'Volante(Primera línea)'
            if (line === 2) return 'Volante(Segunda línea)'

            return 'Volante(Central)'
        }

        if (type === 'attack') {
            const isLeft = x < canvasCenterX - 30
            const isRight = x > canvasCenterX + 30

            if (isLeft) return 'Delantero(Izquierdo)'
            if (isRight) return 'Delantero(Derecho)'
            return 'Delantero(Central)'
        }

        return 'Jugador'
    }

    function updateSpecificRoles() {
        for (const key of posKeys.value) {
            if (positions.value[key]) {
                positions.value[key].specificRole = getSpecificRole(positions.value[key])
            }
        }
    }

    /**
     * Aplica la formación actual y reparte las posiciones con una perspectiva
     * adaptada a la imagen vertical del campo.
     */
    function applyFormation(formationStr) {
        const parsedParts = parseFormationString(formationStr)
        const baseScheme = props.formationsMap[formationStr] || parsedParts
        if (!baseScheme) return

        const scheme = [...baseScheme]
        const fieldPlayers = props.playerCount - (props.includeGoalkeeper ? 1 : 0)
        const totalFormationPlayers = scheme.reduce((sum, number) => sum + number, 0)

        if (totalFormationPlayers !== fieldPlayers) {
            console.warn(`La formación ${formationStr} (${totalFormationPlayers} jugadores) no coincide con el número de jugadores de campo (${fieldPlayers})`)
            adjustFormationToPlayerCount(scheme, fieldPlayers)
        }

        if (props.includeGoalkeeper) {
            positions.value.GK = {
                ...positions.value.GK,
                x: canvasWidth.value / 2,
                y: canvasHeight.value * 0.82,
                key: 'GK'
            }
        }

        const lines = scheme.length
        let index = props.includeGoalkeeper ? 1 : 0
        const lineTypes = getLineTypes(scheme.length)

        for (let line = 0; line < lines; line++) {
            const count = scheme[line]
            const lineType = lineTypes[line]

            let yFrac
            switch (lineType) {
                case 'defense':
                    yFrac = 0.62
                    break
                case 'mid-defensive':
                    yFrac = 0.48
                    break
                case 'midfield':
                    yFrac = 0.3
                    break
                case 'mid-offensive':
                    yFrac = 0.35
                    break
                case 'attack':
                    yFrac = 0.15
                    break
                default:
                    yFrac = 0.75 - (line / Math.max(1, lines - 1)) * 0.5
            }

            const perspectiveFactor = Math.pow((yFrac - 0.15) / 0.5, 0.7)
            const minMargin = 0.08
            const maxMargin = 0.30
            const horizontalMarginFraction = maxMargin - (maxMargin - minMargin) * perspectiveFactor

            const leftMargin = canvasWidth.value * horizontalMarginFraction
            const rightMargin = canvasWidth.value * horizontalMarginFraction
            const usableWidth = canvasWidth.value - leftMargin - rightMargin

            for (let i = 0; i < count; i++) {
                if (index >= posKeys.value.length) break

                const positionRatio = (i + 1) / (count + 1)
                const x = leftMargin + usableWidth * positionRatio
                const posKey = posKeys.value[index]

                positions.value[posKey] = {
                    ...positions.value[posKey],
                    x,
                    y: canvasHeight.value * yFrac,
                    type: lineType,
                    line,
                    order: i,
                    key: posKey
                }

                index++
            }
        }

        while (index < posKeys.value.length) {
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

        updateSpecificRoles()
        emit('update-positions', getPositionsSnapshot())
    }

    function getLineTypes(lineCount) {
        const types = []

        if (lineCount === 1) return ['midfield']
        if (lineCount === 2) return ['defense', 'attack']
        if (lineCount === 3) return ['defense', 'midfield', 'attack']
        if (lineCount === 4) return ['defense', 'mid-defensive', 'midfield', 'attack']
        if (lineCount === 5) return ['defense', 'mid-defensive', 'midfield', 'mid-offensive', 'attack']

        for (let i = 0; i < lineCount; i++) {
            if (i === 0) types.push('defense')
            else if (i === lineCount - 1) types.push('attack')
            else types.push('midfield')
        }

        return types
    }

    function adjustFormationToPlayerCount(scheme, targetPlayerCount) {
        const currentTotal = scheme.reduce((sum, number) => sum + number, 0)
        const difference = targetPlayerCount - currentTotal

        if (difference === 0) return scheme

        if (difference > 0) {
            let remaining = difference
            let lineIndex = 0

            while (remaining > 0) {
                scheme[lineIndex % scheme.length]++
                remaining--
                lineIndex++
            }
        } else {
            let remaining = Math.abs(difference)
            let lineIndex = scheme.length - 1

            while (remaining > 0 && lineIndex >= 0) {
                if (scheme[lineIndex] > 1) {
                    scheme[lineIndex]--
                    remaining--
                }

                lineIndex--
                if (lineIndex < 0) lineIndex = scheme.length - 1
            }
        }

        console.log(`Formación ajustada automáticamente: ${scheme.join('-')}`)
        return scheme
    }

    /* --------------------------------------------------------------------- */
    /* Interacción de usuarios: drag and drop / pointer events               */
    /* --------------------------------------------------------------------- */
    function findNearestPosition(x, y, radius = 40) {
        let best = null
        let bestDist = Infinity

        const positionsInArea = posKeys.value.filter((key) => {
            const position = positions.value[key]
            const dx = position.x - x
            const dy = position.y - y
            return Math.sqrt(dx * dx + dy * dy) < radius * 2
        }).length

        const densityFactor = Math.max(0.5, 1 - (positionsInArea / 20))
        const adjustedRadius = radius * densityFactor

        for (const key of posKeys.value) {
            const position = positions.value[key]
            const penalty = position.assigned ? 20 : 0
            const dx = position.x - x
            const dy = position.y - y
            const distance = Math.sqrt(dx * dx + dy * dy) + penalty

            if (distance < bestDist && distance <= adjustedRadius) {
                bestDist = distance
                best = key
            }
        }

        return best
    }

    function toCanvasPoint(event) {
        const rect = canvas.value?.getBoundingClientRect()
        if (!rect || !rect.width || !rect.height) {
            return { x: 0, y: 0 }
        }

        const scaleX = canvasWidth.value / rect.width
        const scaleY = canvasHeight.value / rect.height

        return {
            x: (event.clientX - rect.left) * scaleX,
            y: (event.clientY - rect.top) * scaleY
        }
    }

    function getAssignedBadgeMetrics(position) {
        const shortName = truncateDisplayText(position?.assigned?.name || '', 14, 12)
        const roleText = position?.specificRole && position.specificRole !== 'Jugador' ? position.specificRole : ''
        const badgeWidth = estimateBadgeWidth(shortName, roleText)
        const badgeHeight = roleText ? 42 : 28

        return {
            badgeWidth,
            badgeHeight,
            badgeX: position.x - badgeWidth / 2,
            badgeY: position.y + PLAYER_LABEL_OFFSET_Y
        }
    }

    function isPointInsideRect(x, y, rect, padding = 0) {
        return (
            x >= rect.badgeX - padding &&
            x <= rect.badgeX + rect.badgeWidth + padding &&
            y >= rect.badgeY - padding &&
            y <= rect.badgeY + rect.badgeHeight + padding
        )
    }

    function isPointNearConnector(x, y, position, badgeMetrics) {
        const topY = position.y + PLAYER_NODE_RADIUS
        const bottomY = badgeMetrics.badgeY

        return (
            x >= position.x - 12 &&
            x <= position.x + 12 &&
            y >= topY &&
            y <= bottomY + 4
        )
    }

    function isPointInsideAssignedHitArea(position, x, y) {
        if (!position?.assigned) return false

        const distance = Math.hypot(position.x - x, position.y - y)
        if (distance <= PLAYER_NODE_RADIUS + 4) {
            return true
        }

        const badgeMetrics = getAssignedBadgeMetrics(position)
        return isPointInsideRect(x, y, badgeMetrics, 14) || isPointNearConnector(x, y, position, badgeMetrics)
    }

    function findAssignedPositionAtPoint(x, y) {
        for (const key of [...posKeys.value].reverse()) {
            const position = positions.value[key]
            if (isPointInsideAssignedHitArea(position, x, y)) {
                return key
            }
        }

        return null
    }

    function resolveInteractivePosition(x, y, radius = 40) {
        return findAssignedPositionAtPoint(x, y) || findNearestPosition(x, y, radius)
    }

    function getSelectionHint(player) {
        if (!player?.name) return DEFAULT_STATUS
        return `${player.name} listo para ubicarse. Toca una posición disponible o arrástralo desde la plantilla.`
    }

    function updateHoverStatus(posKey) {
        hoveredKey.value = posKey

        if (posKey && positions.value[posKey]?.assigned) {
            const position = positions.value[posKey]
            canvasStatus.value = `${position.assigned.name} | ${position.specificRole || posKey}`
            return
        }

        if (posKey) {
            canvasStatus.value = `${positions.value[posKey]?.specificRole || posKey} disponible para asignación.`
            return
        }

        if (props.selectedPlayer) {
            canvasStatus.value = lastSelectionHint || getSelectionHint(props.selectedPlayer)
            return
        }

        canvasStatus.value = DEFAULT_STATUS
    }

    /**
     * Normaliza el jugador arrastrado, resuelve la foto si existe y lo asigna
     * a la posición táctica correspondiente.
     */
    function assignPlayerToPos(playerObj, posKey) {
        if (!playerObj || !posKey) return

        const existingPosition = findPlayerPosition(playerObj.id)
        if (existingPosition && existingPosition !== posKey) {
            const prevPlayer = positions.value[existingPosition].assigned
            positions.value[existingPosition].assigned = null
            emit('unassign-player', { player: prevPlayer, posKey: existingPosition })
        }

        const positionRole = positions.value[posKey].specificRole
        const previous = positions.value[posKey].assigned
        if (previous && (!playerObj.id || previous.id !== playerObj.id)) {
            emit('unassign-player', { player: previous, posKey })
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
            imgLoading: false,
            position: positionRole
        }

        if (clone.img && typeof clone.img === 'string' && clone.img.trim() !== '') {
            clone.imgObj = null

            setTimeout(() => {
                if (positions.value[posKey]?.assigned?.id === clone.id) {
                    const img = new Image()
                    img.onload = () => {
                        if (positions.value[posKey]?.assigned?.id === clone.id) {
                            positions.value[posKey].assigned.imgObj = img
                        }
                    }
                    img.onerror = () => {
                        console.warn('No se pudo cargar la imagen:', clone.img)
                        if (positions.value[posKey]?.assigned?.id === clone.id) {
                            positions.value[posKey].assigned.imgObj = null
                        }
                    }
                    img.src = clone.img
                }
            }, 0)
        } else {
            clone.imgObj = null
        }

        positions.value[posKey].assigned = clone
        emit('assign-player', { player: clone, posKey })
        emit('update-positions', getPositionsSnapshot())
    }

    function findPlayerPosition(playerId) {
        for (const key of posKeys.value) {
            if (positions.value[key].assigned && positions.value[key].assigned.id === playerId) {
                return key
            }
        }

        return null
    }

    function onDrop(event) {
        const raw = event.dataTransfer?.getData('application/json')
        if (!raw) return

        try {
            const player = JSON.parse(raw)
            const { x, y } = toCanvasPoint(event)
            const nearest = findNearestPosition(x, y, 40)

            if (nearest) {
                assignPlayerToPos(player, nearest)
                canvasStatus.value = `${player.name} asignado a ${positions.value[nearest].specificRole || nearest}.`
                hoveredKey.value = nearest
            } else {
                canvasStatus.value = 'Suelta el jugador cerca de un punto táctico para asignarlo.'
            }
        } catch (error) {
            console.error('Error processing drop:', error)
        }
    }

    function startDrag(event) {
        const { x, y } = toCanvasPoint(event)

        for (const key of posKeys.value) {
            if (props.includeGoalkeeper && key === 'GK') continue

            const position = positions.value[key]
            if (isPointInsideAssignedHitArea(position, x, y)) {
                isDragging.value = true
                dragKey.value = key
                hoveredKey.value = key
                dragOffsetX = x - position.x
                dragOffsetY = y - position.y
                canvasStatus.value = `Moviendo a ${position.assigned.name}.`
                return true
            }
        }

        return false
    }

    function onDrag(event) {
        if (!isDragging.value || !dragKey.value) return
        if (props.includeGoalkeeper && dragKey.value === 'GK') return

        const { x, y } = toCanvasPoint(event)
        positions.value[dragKey.value].x = x - dragOffsetX
        positions.value[dragKey.value].y = y - dragOffsetY
    }

    function releaseAssignedPlayer(posKey, statusMessage = null) {
        const previous = positions.value[posKey]?.assigned
        if (!previous) return false

        positions.value[posKey].assigned = null
        emit('unassign-player', { player: previous, posKey })
        emit('update-positions', getPositionsSnapshot())

        if (statusMessage) {
            canvasStatus.value = statusMessage
        }

        return true
    }

    function endDrag(event = null) {
        if (isDragging.value && dragKey.value) {
            suppressNextClick = true
            const activeKey = dragKey.value
            const droppedInPlayerList = event?.clientX != null && event?.clientY != null
                ? document.elementFromPoint(event.clientX, event.clientY)?.closest('.player-panel, .player-list')
                : null

            if (droppedInPlayerList) {
                const playerName = positions.value[activeKey].assigned?.name
                releaseAssignedPlayer(activeKey, `${playerName} regresó a la plantilla disponible.`)
                isDragging.value = false
                dragKey.value = null
                hoveredKey.value = null
                return
            }

            positions.value[activeKey].specificRole = getSpecificRole(positions.value[activeKey])
            const playerName = positions.value[activeKey].assigned?.name
            emit('update-positions', getPositionsSnapshot())

            if (playerName) {
                canvasStatus.value = `${playerName} reubicado en ${positions.value[activeKey].specificRole}.`
            }
        }

        isDragging.value = false
        dragKey.value = null
    }

    function onCanvasPointerDown(event) {
        if (!canvas.value) return

        if (startDrag(event)) {
            dragPointerId = event.pointerId
            canvas.value.setPointerCapture?.(event.pointerId)
        }
    }

    function onCanvasPointerMove(event) {
        if (!canvas.value) return

        const { x, y } = toCanvasPoint(event)
        if (isDragging.value) {
            onDrag(event)
            return
        }

        const nearest = resolveInteractivePosition(x, y, 36)
        updateHoverStatus(nearest)
    }

    function onCanvasPointerUp(event) {
        if (isDragging.value) {
            endDrag(event)
        }

        if (dragPointerId != null) {
            canvas.value?.releasePointerCapture?.(dragPointerId)
            dragPointerId = null
        } else if (event?.pointerId != null) {
            canvas.value?.releasePointerCapture?.(event.pointerId)
        }
    }

    function onCanvasPointerLeave() {
        if (isDragging.value) return
        updateHoverStatus(null)
    }

    function onCanvasClick(event) {
        if (suppressNextClick) {
            suppressNextClick = false
            return
        }

        if (!props.selectedPlayer) return

        const { x, y } = toCanvasPoint(event)
        const target = resolveInteractivePosition(x, y, 54)

        if (!target) {
            lastSelectionHint = `Acerca ${props.selectedPlayer.name} a un punto táctico o toca una posición disponible.`
            canvasStatus.value = lastSelectionHint
            return
        }

        assignPlayerToPos(props.selectedPlayer, target)
        hoveredKey.value = target
        canvasStatus.value = `${props.selectedPlayer.name} asignado a ${positions.value[target].specificRole || target}.`
        emit('clear-selected-player')
    }

    function onDblClick(event) {
        const { x, y } = toCanvasPoint(event)
        const nearest = resolveInteractivePosition(x, y, 50)
        if (!nearest) return

        const previous = positions.value[nearest].assigned
        if (previous) {
            hoveredKey.value = nearest
            releaseAssignedPlayer(nearest, `${previous.name} regresó a la lista de disponibles.`)
        }
    }

    /* --------------------------------------------------------------------- */
    /* Ayudas visuales para SVG y exportación                                */
    /* --------------------------------------------------------------------- */
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

    function truncateDisplayText(text, threshold = 10, visible = 8) {
        if (!text) return ''
        if (text.length <= threshold) return text
        return `${text.substring(0, visible)}…`
    }

    function estimateBadgeWidth(shortName, roleText) {
        const shortNameWidth = shortName ? shortName.length * 7.4 + 24 : 90
        const roleWidth = roleText ? roleText.length * 5.9 + 24 : 0
        return Math.max(90, shortNameWidth, roleWidth)
    }

    function drawBackgroundOverlay(targetCtx) {
        targetCtx.save()

        const topShade = targetCtx.createLinearGradient(0, 0, 0, canvasHeight.value * 0.25)
        topShade.addColorStop(0, 'rgba(0, 0, 0, 0.22)')
        topShade.addColorStop(1, 'rgba(0, 0, 0, 0)')
        targetCtx.fillStyle = topShade
        targetCtx.fillRect(0, 0, canvasWidth.value, canvasHeight.value * 0.25)

        const bottomShade = targetCtx.createLinearGradient(0, canvasHeight.value, 0, canvasHeight.value * 0.76)
        bottomShade.addColorStop(0, 'rgba(0, 0, 0, 0.26)')
        bottomShade.addColorStop(1, 'rgba(0, 0, 0, 0)')
        targetCtx.fillStyle = bottomShade
        targetCtx.fillRect(0, canvasHeight.value * 0.76, canvasWidth.value, canvasHeight.value * 0.24)

        targetCtx.restore()
    }

    function drawPositionNode(targetCtx, position, palette, isHovered = false) {
        const radius = isHovered ? PLAYER_NODE_HOVER_RADIUS : PLAYER_NODE_RADIUS

        targetCtx.save()
        targetCtx.shadowColor = palette.glow
        targetCtx.shadowBlur = isHovered ? 26 : 16
        targetCtx.shadowOffsetX = 0
        targetCtx.shadowOffsetY = 8

        const gradient = targetCtx.createRadialGradient(position.x - 8, position.y - 10, 8, position.x, position.y, radius)
        gradient.addColorStop(0, '#ffffff')
        gradient.addColorStop(0.2, palette.fill)
        gradient.addColorStop(1, palette.stroke)

        targetCtx.beginPath()
        targetCtx.fillStyle = gradient
        targetCtx.strokeStyle = '#ffffff'
        targetCtx.lineWidth = isHovered ? 3 : 2
        targetCtx.arc(position.x, position.y, radius, 0, Math.PI * 2)
        targetCtx.fill()
        targetCtx.stroke()
        targetCtx.restore()

        if (isHovered) {
            targetCtx.save()
            targetCtx.beginPath()
            targetCtx.strokeStyle = 'rgba(255, 255, 255, 0.75)'
            targetCtx.lineWidth = 2
            targetCtx.arc(position.x, position.y, radius + 7, 0, Math.PI * 2)
            targetCtx.stroke()
            targetCtx.restore()
        }
    }

    function drawDefaultMarker(targetCtx, x, y, label) {
        targetCtx.fillStyle = '#0b1d17'
        targetCtx.font = '700 13px "Trebuchet MS", Arial, sans-serif'
        targetCtx.textAlign = 'center'
        targetCtx.textBaseline = 'middle'
        targetCtx.fillText(truncateDisplayText(label || '', 10, 8), x, y)
    }

    function drawPlayerName(targetCtx, x, y, name, specificRole = null) {
        const shortText = truncateDisplayText(name || '', 14, 12)
        const roleText = specificRole && specificRole !== 'Jugador' ? specificRole : ''

        targetCtx.font = '700 13px "Trebuchet MS", Arial, sans-serif'
        targetCtx.textAlign = 'center'
        targetCtx.textBaseline = 'middle'

        const textWidth = targetCtx.measureText(shortText).width
        const roleWidth = roleText ? targetCtx.measureText(roleText).width : 0
        const padding = 12
        const totalWidth = Math.max(textWidth + padding * 2, roleText ? roleWidth + 24 : 90)
        const totalHeight = roleText ? 42 : 28
        const badgeX = x - totalWidth / 2
        const badgeY = y + PLAYER_LABEL_OFFSET_Y

        targetCtx.save()
        targetCtx.shadowColor = 'rgba(0, 0, 0, 0.28)'
        targetCtx.shadowBlur = 10
        targetCtx.shadowOffsetX = 0
        targetCtx.shadowOffsetY = 4

        const gradient = targetCtx.createLinearGradient(badgeX, badgeY, badgeX, badgeY + totalHeight)
        gradient.addColorStop(0, 'rgba(12, 28, 22, 0.9)')
        gradient.addColorStop(1, 'rgba(7, 18, 14, 0.94)')

        roundRect(targetCtx, badgeX, badgeY, totalWidth, totalHeight, 15)
        targetCtx.fillStyle = gradient
        targetCtx.fill()
        targetCtx.restore()

        targetCtx.strokeStyle = 'rgba(255, 255, 255, 0.7)'
        targetCtx.lineWidth = 1.6
        roundRect(targetCtx, badgeX, badgeY, totalWidth, totalHeight, 15)
        targetCtx.stroke()

        targetCtx.fillStyle = 'white'
        targetCtx.font = '700 13px "Trebuchet MS", Arial, sans-serif'
        targetCtx.fillText(shortText, x, badgeY + (roleText ? 13 : totalHeight / 2))

        if (roleText) {
            targetCtx.fillStyle = 'rgba(194, 231, 212, 0.9)'
            targetCtx.font = '600 10px "Trebuchet MS", Arial, sans-serif'
            targetCtx.fillText(roleText, x, badgeY + 29)
        }

        targetCtx.strokeStyle = 'rgba(255, 255, 255, 0.8)'
        targetCtx.lineWidth = 1.5
        targetCtx.beginPath()
        targetCtx.moveTo(x, y + PLAYER_NODE_RADIUS)
        targetCtx.lineTo(x, badgeY)
        targetCtx.stroke()
    }

    function roundRect(targetCtx, x, y, width, height, radius) {
        targetCtx.beginPath()
        targetCtx.moveTo(x + radius, y)
        targetCtx.lineTo(x + width - radius, y)
        targetCtx.quadraticCurveTo(x + width, y, x + width, y + radius)
        targetCtx.lineTo(x + width, y + height - radius)
        targetCtx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height)
        targetCtx.lineTo(x + radius, y + height)
        targetCtx.quadraticCurveTo(x, y + height, x, y + height - radius)
        targetCtx.lineTo(x, y + radius)
        targetCtx.quadraticCurveTo(x, y, x + radius, y)
        targetCtx.closePath()
    }

    function buildFieldCanvas() {
        const fieldCanvas = document.createElement('canvas')
        fieldCanvas.width = canvasWidth.value
        fieldCanvas.height = canvasHeight.value

        const targetCtx = fieldCanvas.getContext('2d')
        if (!targetCtx) return null

        targetCtx.imageSmoothingEnabled = true
        targetCtx.clearRect(0, 0, canvasWidth.value, canvasHeight.value)

        if (fieldImg.complete) {
            targetCtx.drawImage(fieldImg, 0, 0, canvasWidth.value, canvasHeight.value)
        } else {
            targetCtx.fillStyle = '#47a447'
            targetCtx.fillRect(0, 0, canvasWidth.value, canvasHeight.value)
        }

        drawBackgroundOverlay(targetCtx)

        for (const key of posKeys.value) {
            const position = positions.value[key]
            const palette = getMarkerPalette(position.type)

            drawPositionNode(targetCtx, position, palette, false)

            if (position.assigned) {
                if (position.assigned.imgObj && position.assigned.imgObj instanceof Image && position.assigned.imgObj.complete) {
                    try {
                        targetCtx.save()
                        targetCtx.beginPath()
                        targetCtx.arc(position.x, position.y, PLAYER_IMAGE_RADIUS, 0, Math.PI * 2)
                        targetCtx.closePath()
                        targetCtx.clip()
                        targetCtx.drawImage(
                            position.assigned.imgObj,
                            position.x - PLAYER_IMAGE_RADIUS,
                            position.y - PLAYER_IMAGE_RADIUS,
                            PLAYER_IMAGE_RADIUS * 2,
                            PLAYER_IMAGE_RADIUS * 2
                        )
                        targetCtx.restore()
                    } catch (error) {
                        console.error('Error dibujando imagen:', error)
                        drawDefaultMarker(targetCtx, position.x, position.y, position.assigned.name)
                    }
                } else {
                    drawDefaultMarker(targetCtx, position.x, position.y, position.assigned.name)
                }

                drawPlayerName(targetCtx, position.x, position.y, position.assigned.name, position.specificRole)
            } else {
                drawDefaultMarker(targetCtx, position.x, position.y, position.specificRole || key)
            }
        }

        return fieldCanvas
    }

    /* --------------------------------------------------------------------- */
    /* Snapshot y exportación                                                */
    /* --------------------------------------------------------------------- */
    function getPositionsSnapshot() {
        const snapshot = {}

        for (const key of posKeys.value) {
            snapshot[key] = {
                x: positions.value[key].x,
                y: positions.value[key].y,
                type: positions.value[key].type || 'midfield',
                line: positions.value[key].line || 0,
                order: positions.value[key].order || 0,
                key: positions.value[key].key,
                specificRole: positions.value[key].specificRole || getSpecificRole(positions.value[key]),
                assigned: positions.value[key].assigned ? {
                    id: positions.value[key].assigned.id,
                    name: positions.value[key].assigned.name,
                    number: positions.value[key].assigned.number,
                    img: positions.value[key].assigned.img
                } : null
            }
        }

        return {
            formation: props.formation,
            playerCount: props.playerCount,
            includeGoalkeeper: props.includeGoalkeeper,
            canvasWidth: canvasWidth.value,
            canvasHeight: canvasHeight.value,
            positions: snapshot
        }
    }

    /**
     * Reinicia la cancha, limpia titulares y devuelve el control al padre
     * para que vuelva a poblar la plantilla disponible.
     */
    function resetToDefault() {
        let hadAssignedPlayers = false

        for (const key of posKeys.value) {
            if (positions.value[key]?.assigned) {
                hadAssignedPlayers = true
                positions.value[key].assigned = null
            }
        }

        if (hadAssignedPlayers) {
            emit('reset-lineup')
        }

        applyFormation(props.formation)
        hoveredKey.value = null
        canvasStatus.value = 'Cancha reiniciada a la distribución base.'
    }

    function getCanvasImage(format = 'png', quality = 0.9) {
        const exportCanvas = buildExportCanvas()
        if (!exportCanvas) return null

        const dataUrl = exportCanvas.toDataURL(`image/${format}`, quality)
        return {
            base64: dataUrl,
            format,
            width: exportCanvas.width,
            height: exportCanvas.height
        }
    }

    /**
     * Construye un canvas compuesto: cancha arriba y convocatoria abajo.
     * Así la imagen descargada sirve tanto para táctica como para compartirla.
     */
    function buildExportCanvas() {
        const fieldCanvas = buildFieldCanvas()
        if (!fieldCanvas) return null

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
        if (!exportCtx) return null

        exportCtx.imageSmoothingEnabled = true
        exportCtx.drawImage(fieldCanvas, 0, 0)

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

    function saveCanvasImage() {
        const canvasImage = getCanvasImage()
        if (!canvasImage) return

        const link = document.createElement('a')
        link.href = canvasImage.base64
        link.download = `formacion_${props.formation}_${new Date().toISOString().slice(0, 10)}.png`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        canvasStatus.value = 'Imagen PNG descargada con convocatoria.'
    }

    /* --------------------------------------------------------------------- */
    /* Ciclo de vida y sincronización                                        */
    /* --------------------------------------------------------------------- */
    watch(() => props.formation, (nextFormation) => {
        applyFormation(nextFormation)
    })

    watch(() => props.playerCount, () => {
        initializePositions()
        applyFormation(props.formation)
    })

    watch(() => props.selectedPlayer, (player) => {
        if (player) {
            lastSelectionHint = getSelectionHint(player)
            canvasStatus.value = lastSelectionHint
            return
        }

        if (!isDragging.value && canvasStatus.value === lastSelectionHint) {
            canvasStatus.value = DEFAULT_STATUS
        }
    })

    onMounted(() => {
        if (fieldImg.complete) {
            canvasWidth.value = fieldImg.naturalWidth || DEFAULT_FIELD_WIDTH
            canvasHeight.value = fieldImg.naturalHeight || DEFAULT_FIELD_HEIGHT
            initializePositions()
            applyFormation(props.formation)
        }
    })

    /**
     * API pública que consume `Field.vue`:
     * - refs/estado para el template.
     * - handlers de interacción del SVG.
     * - métodos expuestos al padre mediante `defineExpose`.
     */
    return {
        canvas,
        canvasWidth,
        canvasHeight,
        canvasStatus,
        assignedCount,
        openSlots,
        clipPathPrefix,
        fieldViewBox,
        fieldImageSrc: fieldImg.src,
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
    }
}
