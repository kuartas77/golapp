import { onMounted, reactive, ref, watch } from 'vue'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'

export default function usePaymentReport() {
    usePageTitle('Informe de pagos')

    const isLoading = ref(false)
    const isSubmitting = ref(false)
    const loadError = ref(null)
    const hasBootstrapped = ref(false)
    const years = ref([])
    const groups = ref([])

    const form = reactive({
        year: null,
        training_group_id: null,
    })

    const loadOptions = async (requestedYear = null) => {
        isLoading.value = true
        loadError.value = null

        try {
            const response = await api.get('/api/v2/reports/payments', {
                params: {
                    year: requestedYear ?? form.year,
                },
            })

            years.value = response.data.years ?? []
            groups.value = response.data.groups ?? []

            const resolvedYear = requestedYear
                ?? form.year
                ?? response.data.defaultYear
                ?? years.value[years.value.length - 1]?.value
                ?? new Date().getFullYear()

            form.year = resolvedYear

            if (!groups.value.some((group) => group.value === form.training_group_id)) {
                form.training_group_id = null
            }
        } catch (error) {
            years.value = []
            groups.value = []
            loadError.value = error.response?.data?.message || 'No fue posible cargar las opciones del informe.'
        } finally {
            isLoading.value = false
        }
    }

    const sendByEmail = async () => {
        if (!form.year) {
            showMessage('Selecciona un año para exportar.', 'error')
            return
        }

        isSubmitting.value = true

        try {
            const payload = {
                year: form.year,
            }

            if (form.training_group_id) {
                payload.training_group_id = form.training_group_id
            }

            const response = await api.post('/reports/payments', payload)
            showMessage(response.data?.message || 'El archivo será enviado al correo electrónico registrado.')
        } catch (error) {
            const message = error.response?.data?.message || 'No fue posible generar el informe.'
            showMessage(message, 'error')
        } finally {
            isSubmitting.value = false
        }
    }

    watch(
        () => form.year,
        async (year, previousYear) => {
            if (!hasBootstrapped.value || !year || year === previousYear) {
                return
            }

            await loadOptions(year)
        }
    )

    onMounted(async () => {
        await loadOptions()
        hasBootstrapped.value = true
    })

    return {
        form,
        groups,
        isLoading,
        isSubmitting,
        loadError,
        sendByEmail,
        years,
    }
}
