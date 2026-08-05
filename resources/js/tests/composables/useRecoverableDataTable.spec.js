import { ref } from 'vue'
import { describe, expect, it, vi } from 'vitest'
import { useRecoverableDataTable } from '@/composables/useRecoverableDataTable'

describe('useRecoverableDataTable', () => {
    it('prefers the backend message and clears it after a successful request', () => {
        const table = ref(null)
        const state = useRecoverableDataTable(table, 'Mensaje alternativo')

        state.handleError({ response: { data: { message: 'Servicio temporalmente no disponible.' } } })
        expect(state.globalError.value).toBe('Servicio temporalmente no disponible.')

        state.clearError()
        expect(state.globalError.value).toBe('')
    })

    it('uses the fallback and reloads with a fresh pipeline', async () => {
        const clearPipeline = vi.fn()
        const draw = vi.fn()
        clearPipeline.mockReturnValue({ draw })
        const table = ref({
            table: {
                dt: {
                    clearPipeline,
                },
            },
        })
        const state = useRecoverableDataTable(table, 'No fue posible cargar el listado.')

        state.handleError({})
        expect(state.globalError.value).toBe('No fue posible cargar el listado.')

        await state.reloadTable()
        expect(state.globalError.value).toBe('')
        expect(clearPipeline).toHaveBeenCalledOnce()
        expect(draw).toHaveBeenCalledWith(false)
    })

    it('keeps an actionable error when the table is not ready to reload', async () => {
        const state = useRecoverableDataTable(ref(null), 'No fue posible cargar el listado.')

        await state.reloadTable()

        expect(state.globalError.value).toBe('No fue posible cargar el listado.')
    })

    it('remounts an identified table when its DataTables instance is not ready', async () => {
        const state = useRecoverableDataTable(ref(null), 'No fue posible cargar el listado.', 'delayed-table')

        state.handleError({})
        await state.reloadTable()

        expect(state.globalError.value).toBe('')
        expect(state.tableKey.value).toBe(1)
    })
})
