import { nextTick, ref } from 'vue'

export function useRecoverableDataTable(table, fallbackMessage) {
    const globalError = ref('')

    const clearError = () => {
        globalError.value = ''
    }

    const handleError = (error) => {
        globalError.value = error.response?.data?.message || fallbackMessage
    }

    const reloadTable = async () => {
        let dt = null

        for (let attempt = 0; attempt < 10 && !dt; attempt += 1) {
            await nextTick()
            dt = table.value?.table?.dt

            if (!dt) {
                await new Promise(resolve => setTimeout(resolve, 10))
            }
        }

        if (dt) {
            clearError()
            dt.clearPipeline()
            dt.ajax.reload(null, false)
            return
        }

        globalError.value = fallbackMessage
    }

    return {
        globalError,
        clearError,
        handleError,
        reloadTable,
    }
}
