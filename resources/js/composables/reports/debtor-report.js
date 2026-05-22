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

export default function useDebtorReport() {
    usePageTitle('Informe de deudores')

    const route = useRoute()

    const isLoading = ref(false)
    const loadError = ref(null)
    const hasBootstrapped = ref(false)
    const years = ref([])
    const groups = ref([])

    const form = reactive({
        year: null,
        training_group_id: null,
        show_total_debt: false,
    })

    const exportUrl = computed(() => {
        if (!form.year) {
            return ''
        }

        const params = new URLSearchParams({
            year: String(form.year),
        })

        if (form.training_group_id) {
            params.set('training_group_id', String(form.training_group_id))
        }

        if (form.show_total_debt) {
            params.set('show_total_debt', '1')
        }

        return `/api/v2/reports/debtors/pdf?${params.toString()}`
    })

    const loadOptions = async (requestedYear = null) => {
        isLoading.value = true
        loadError.value = null

        try {
            const response = await api.get('/api/v2/reports/debtors', {
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

    const exportPdf = () => {
        if (!form.year) {
            showMessage('Selecciona un año para exportar.', 'error')
            return
        }

        window.open(exportUrl.value, '_blank', 'noopener')
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
        exportPdf,
        exportUrl,
        form,
        groups,
        isLoading,
        loadError,
        years,
    }
}
