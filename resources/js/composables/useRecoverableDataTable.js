import { ref } from 'vue'

export function useRecoverableDataTable(table, fallbackMessage) {
    const globalError = ref('')

    const clearError = () => {
        globalError.value = ''
    }

    const handleError = (error) => {
        globalError.value = error.response?.data?.message || fallbackMessage
    }

    const reloadTable = () => {
        clearError()
        const dt = table.value?.table?.dt

        if (dt) {
            dt.clearPipeline()
            dt.ajax.reload(null, false)
        }
    }

    return {
        globalError,
        clearError,
        handleError,
        reloadTable,
    }
}
