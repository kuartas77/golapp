import { computed, onMounted, reactive, ref, watch } from 'vue'
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

export default function useReceivedPaymentReport() {
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
        player_search: '',
        show_item_amounts: true,
        show_total_paid: true,
    })

    const exportUrl = computed(() => {
        if (!form.year) {
            return ''
        }

        const params = new URLSearchParams({
            year: String(form.year),
            show_item_amounts: form.show_item_amounts ? '1' : '0',
            show_total_paid: form.show_total_paid ? '1' : '0',
        })

        if (form.training_group_id) {
            params.set('training_group_id', String(form.training_group_id))
        }

        if (form.player_search.trim()) {
            params.set('player_search', form.player_search.trim())
        }

        return `/api/v2/reports/received-payments/pdf?${params.toString()}`
    })

    const loadOptions = async (requestedYear = null) => {
        isLoading.value = true
        loadError.value = null

        try {
            const response = await api.get('/api/v2/reports/received-payments', {
                params: { year: requestedYear ?? form.year },
            })

            years.value = response.data.years ?? []
            groups.value = response.data.groups ?? []
            form.year = requestedYear
                ?? form.year
                ?? response.data.defaultYear
                ?? years.value[years.value.length - 1]?.value
                ?? new Date().getFullYear()

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

    const exportReport = async () => {
        if (!form.year) {
            showMessage('Selecciona un año para exportar.', 'error')
            return
        }

        if (form.training_group_id) {
            window.open(exportUrl.value, '_blank', 'noopener')
            return
        }

        isSubmitting.value = true

        try {
            const response = await api.post('/api/v2/reports/received-payments', {
                year: form.year,
                player_search: form.player_search.trim(),
                show_item_amounts: form.show_item_amounts,
                show_total_paid: form.show_total_paid,
            })
            showMessage(response.data?.message || 'El informe será enviado al correo electrónico registrado.')
        } catch (error) {
            showMessage(error.response?.data?.message || 'No fue posible solicitar el informe.', 'error')
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
        exportReport,
        exportUrl,
        form,
        groups,
        isLoading,
        isSubmitting,
        loadError,
        years,
    }
}
