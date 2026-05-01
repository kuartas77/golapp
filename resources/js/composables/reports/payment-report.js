import { onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'

const parseQueryNumber = (value) => {
    const normalizedValue = Array.isArray(value) ? value[0] : value

    if (normalizedValue === null || normalizedValue === undefined || normalizedValue === '') {
        return null
    }

    const parsed = Number(normalizedValue)

    return Number.isInteger(parsed) && parsed > 0 ? parsed : null
}

export default function usePaymentReport() {
    usePageTitle('Informe de pagos')

    const route = useRoute()

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

            const response = await api.post('/api/v2/reports/payments', payload)
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
        form.training_group_id = parseQueryNumber(route.query.training_group_id)
        await loadOptions(parseQueryNumber(route.query.year))
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
