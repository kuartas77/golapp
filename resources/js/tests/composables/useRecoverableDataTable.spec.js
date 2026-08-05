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

    it('uses the fallback and reloads with a fresh pipeline', () => {
        const clearPipeline = vi.fn()
        const reload = vi.fn()
        const table = ref({
            table: {
                dt: {
                    clearPipeline,
                    ajax: { reload },
                },
            },
        })
        const state = useRecoverableDataTable(table, 'No fue posible cargar el listado.')

        state.handleError({})
        expect(state.globalError.value).toBe('No fue posible cargar el listado.')

        state.reloadTable()
        expect(state.globalError.value).toBe('')
        expect(clearPipeline).toHaveBeenCalledOnce()
        expect(reload).toHaveBeenCalledWith(null, false)
    })
})
