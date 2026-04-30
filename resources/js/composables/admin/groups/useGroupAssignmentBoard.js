import { computed, onMounted, ref } from 'vue'
import api from '@/utils/axios'

const DEFAULT_PANEL = () => ({
    group_id: null,
    group_label: null,
    count: 0,
    items: [],
})

const MODE_CONFIG = {
    training: {
        boardUrl: '/api/v2/admin/training-groups/board',
        moveUrl: '/api/v2/admin/training-groups/move',
        loadError: 'No fue posible cargar la conformación de grupos de entrenamiento.',
        moveError: 'No fue posible actualizar el grupo de entrenamiento.',
    },
    competition: {
        boardUrl: '/api/v2/admin/competition-groups/board',
        moveUrl: '/api/v2/admin/competition-groups/move',
        loadError: 'No fue posible cargar la conformación de grupos de competencia.',
        moveError: 'No fue posible actualizar el grupo de competencia.',
    },
}

const cloneItems = (items) => items.map((item) => ({ ...item }))

export default function useGroupAssignmentBoard(mode) {
    const config = MODE_CONFIG[mode]
    const isTraining = mode === 'training'
    const isCompetition = mode === 'competition'

    const isLoading = ref(false)
    const globalError = ref('')

    const selectors = ref({
        origin_groups: [],
        destination_groups: [],
        groups: [],
    })

    const sourcePanel = ref(DEFAULT_PANEL())
    const destinationPanel = ref(DEFAULT_PANEL())

    const sourceItems = ref([])
    const destinationItems = ref([])

    const sourceSearch = ref('')
    const destinationSearch = ref('')

    const selectedOriginGroupId = ref('')
    const selectedDestinationGroupId = ref('')
    const selectedCompetitionGroupId = ref('')

    const dragSnapshot = ref(null)

    const showSearch = computed(() => isCompetition)
    const sourceVisibleCount = computed(() => sourceItems.value.filter((item) => matchesSearch(item, sourceSearch.value)).length)
    const destinationVisibleCount = computed(() => destinationItems.value.filter((item) => matchesSearch(item, destinationSearch.value)).length)
    const selectedDestinationLabel = computed(() => destinationPanel.value.group_label || 'Ninguno...')

    const loadBoard = async () => {
        isLoading.value = true
        globalError.value = ''

        try {
            const response = await api.get(config.boardUrl, {
                params: buildParams(),
                skipGlobalLoader: true,
            })

            applyBoard(response.data?.data ?? {})
        } catch (error) {
            globalError.value = error.response?.data?.message || config.loadError
            sourcePanel.value = DEFAULT_PANEL()
            destinationPanel.value = DEFAULT_PANEL()
            sourceItems.value = []
            destinationItems.value = []
        } finally {
            isLoading.value = false
        }
    }

    const onOriginChange = async () => {
        if (selectedOriginGroupId.value && selectedOriginGroupId.value === selectedDestinationGroupId.value) {
            selectedOriginGroupId.value = ''
            sourcePanel.value = DEFAULT_PANEL()
            sourceItems.value = []
            triggerMessage('Los grupos seleccionados son los mismos.', 'warning')
            return
        }

        await loadBoard()
    }

    const onDestinationChange = async () => {
        if (selectedDestinationGroupId.value && selectedDestinationGroupId.value === selectedOriginGroupId.value) {
            selectedDestinationGroupId.value = ''
            destinationPanel.value = DEFAULT_PANEL()
            destinationItems.value = []
            triggerMessage('Los grupos seleccionados son los mismos.', 'warning')
            return
        }

        await loadBoard()
    }

    const onCompetitionGroupChange = async () => {
        await loadBoard()
    }

    const onDragStart = () => {
        dragSnapshot.value = {
            source: cloneItems(sourceItems.value),
            destination: cloneItems(destinationItems.value),
        }
    }

    const onDragEnd = async (event) => {
        if (!event?.to || !event?.from || event.to === event.from) {
            dragSnapshot.value = null
            return
        }

        const inscriptionId = Number(event.item?.dataset?.inscriptionId)
        const targetPanel = event.to.dataset?.panel

        if (!inscriptionId || !targetPanel) {
            restoreSnapshot()
            return
        }

        const payload = resolveMovePayload(targetPanel, inscriptionId)

        if (!payload) {
            restoreSnapshot()
            return
        }

        try {
            const response = await api.post(config.moveUrl, payload, {
                skipGlobalLoader: true,
            })

            triggerMessage(response.data?.message || 'Actualizado correctamente.')
            await loadBoard()
            dragSnapshot.value = null
        } catch (error) {
            restoreSnapshot()
            triggerMessage(error.response?.data?.message || config.moveError, 'error')
        }
    }

    const clearError = () => {
        globalError.value = ''
    }

    onMounted(() => {
        loadBoard()
    })

    return {
        clearError,
        destinationItems,
        destinationPanel,
        destinationSearch,
        destinationVisibleCount,
        globalError,
        isCompetition,
        isLoading,
        isTraining,
        loadBoard,
        onCompetitionGroupChange,
        onDestinationChange,
        onDragEnd,
        onDragStart,
        onOriginChange,
        selectedCompetitionGroupId,
        selectedDestinationGroupId,
        selectedDestinationLabel,
        selectedOriginGroupId,
        selectors,
        showSearch,
        sourceItems,
        sourcePanel,
        sourceSearch,
        sourceVisibleCount,
    }

    function applyBoard(board) {
        selectors.value = {
            origin_groups: board.selectors?.origin_groups ?? [],
            destination_groups: board.selectors?.destination_groups ?? [],
            groups: board.selectors?.groups ?? [],
        }

        sourcePanel.value = hydratePanel(board.panels?.source, 'source')
        destinationPanel.value = hydratePanel(board.panels?.destination, 'destination')

        sourceItems.value = sourcePanel.value.items
        destinationItems.value = destinationPanel.value.items

        syncSelectedValues()
    }

    function buildParams() {
        if (isTraining) {
            return {
                origin_group_id: selectedOriginGroupId.value || undefined,
                target_group_id: selectedDestinationGroupId.value || undefined,
            }
        }

        return {
            competition_group_id: selectedCompetitionGroupId.value || undefined,
        }
    }

    function hydratePanel(panel, key) {
        const items = Array.isArray(panel?.items)
            ? panel.items.map((item, index) => ({
                ...item,
                client_key: `${key}-${item.id}-${index}`,
            }))
            : []

        return {
            group_id: panel?.group_id ?? null,
            group_label: panel?.group_label ?? null,
            count: Number(panel?.count ?? items.length),
            items,
        }
    }

    function matchesSearch(item, query) {
        const normalizedQuery = String(query || '').trim().toLowerCase()

        if (!normalizedQuery) {
            return true
        }

        return String(item.search_text || '').toLowerCase().includes(normalizedQuery)
    }

    function resolveMovePayload(targetPanel, inscriptionId) {
        if (isTraining) {
            if (!selectedOriginGroupId.value) {
                triggerMessage('Debes seleccionar un grupo de origen.', 'warning')
                return null
            }

            if (!selectedDestinationGroupId.value) {
                triggerMessage('Debes seleccionar un grupo de destino.', 'warning')
                return null
            }

            if (selectedOriginGroupId.value === selectedDestinationGroupId.value) {
                triggerMessage('Los grupos seleccionados son los mismos.', 'warning')
                return null
            }

            const targetGroupId = targetPanel === 'destination'
                ? selectedDestinationGroupId.value
                : selectedOriginGroupId.value

            return {
                inscription_id: inscriptionId,
                target_group_id: Number(targetGroupId),
            }
        }

        if (!selectedCompetitionGroupId.value) {
            triggerMessage('Debes seleccionar un grupo de competencia.', 'warning')
            return null
        }

        return {
            inscription_id: inscriptionId,
            competition_group_id: Number(selectedCompetitionGroupId.value),
            assign: targetPanel === 'destination',
        }
    }

    function restoreSnapshot() {
        if (!dragSnapshot.value) {
            return
        }

        sourceItems.value = cloneItems(dragSnapshot.value.source)
        destinationItems.value = cloneItems(dragSnapshot.value.destination)
        dragSnapshot.value = null
    }

    function syncSelectedValues() {
        if (isTraining) {
            const originIds = new Set(selectors.value.origin_groups.map((group) => String(group.value)))
            const destinationIds = new Set(selectors.value.destination_groups.map((group) => String(group.value)))

            if (selectedOriginGroupId.value && !originIds.has(String(selectedOriginGroupId.value))) {
                selectedOriginGroupId.value = ''
            }

            if (selectedDestinationGroupId.value && !destinationIds.has(String(selectedDestinationGroupId.value))) {
                selectedDestinationGroupId.value = ''
            }

            return
        }

        const groupIds = new Set(selectors.value.groups.map((group) => String(group.value)))

        if (selectedCompetitionGroupId.value && !groupIds.has(String(selectedCompetitionGroupId.value))) {
            selectedCompetitionGroupId.value = ''
        }
    }

    function triggerMessage(message, type = 'success') {
        if (typeof window.showMessage === 'function') {
            window.showMessage(message, type)
        }
    }
}
