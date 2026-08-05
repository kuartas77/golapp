import { nextTick, ref } from 'vue'
import { DataTablesCore } from '@/plugins/datatables'

export function useRecoverableDataTable(table, fallbackMessage, tableId = '') {
    const globalError = ref('')
    const tableKey = ref(0)

    const clearError = () => {
        globalError.value = ''
    }

    const handleError = (error) => {
        globalError.value = error.response?.data?.message || fallbackMessage
    }

    const resolveDataTable = () => {
        const exposedDataTable = table.value?.table?.dt

        if (exposedDataTable) {
            return exposedDataTable
        }

        if (!tableId || typeof document === 'undefined') {
            return null
        }

        const selector = `#${tableId}`

        return DataTablesCore.isDataTable(selector)
            ? new DataTablesCore.Api(selector)
            : null
    }

    const reloadTable = async () => {
        let dt = null

        for (let attempt = 0; attempt < 10 && !dt; attempt += 1) {
            await nextTick()
            dt = resolveDataTable()

            if (!dt) {
                await new Promise(resolve => setTimeout(resolve, 20))
            }
        }

        if (dt) {
            clearError()
            dt.clearPipeline().draw(false)
            return
        }

        if (tableId) {
            clearError()
            tableKey.value += 1
            return
        }

        globalError.value = fallbackMessage
    }

    return {
        globalError,
        tableKey,
        clearError,
        handleError,
        reloadTable,
    }
}
