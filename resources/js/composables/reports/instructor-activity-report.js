import { computed, onMounted, reactive, ref, useTemplateRef } from 'vue'
import { useRoute } from 'vue-router'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'

const emptyDataTableResponse = (draw = 0) => ({
    draw,
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
})

const normalizeOptions = (options) => {
    if (Array.isArray(options)) {
        return options
    }

    if (options && typeof options === 'object') {
        return Object.values(options)
    }

    return []
}

const normalizeFilters = (filters) =>
    Object.entries(filters).reduce((accumulator, [key, value]) => {
        if (value !== null && value !== '' && value !== undefined) {
            accumulator[key] = value
        }

        return accumulator
    }, {})

const parseQueryNumber = (value) => {
    const normalizedValue = Array.isArray(value) ? value[0] : value

    if (normalizedValue === null || normalizedValue === undefined || normalizedValue === '') {
        return null
    }

    const parsed = Number(normalizedValue)

    return Number.isInteger(parsed) && parsed > 0 ? parsed : null
}

const buildExportUrl = (format, filters) => {
    const query = new URLSearchParams(normalizeFilters(filters)).toString()

    return query
        ? `/export/instructor-activity/${format}?${query}`
        : `/export/instructor-activity/${format}`
}

export default function useInstructorActivityReport() {
    usePageTitle('Informe de actividad de instructores')

    const route = useRoute()
    const table = useTemplateRef('instructorActivityTable')

    const isReady = ref(false)
    const isLoading = ref(false)
    const loadError = ref(null)
    const years = ref([])
    const months = ref([])
    const instructors = ref([])

    const filters = reactive({
        year: null,
        month: null,
        instructor_id: null,
    })

    const columns = [
        { data: 'instructor_name', name: 'instructor_name', title: 'Instructor' },
        { data: 'year', name: 'year', title: 'Año' },
        { data: 'month_label', name: 'month_label', title: 'Mes' },
        { data: 'attendance_coverage', name: 'attendance_coverage', title: 'Asistencias tomadas/por tomar' },
        { data: 'matches_count', name: 'matches_count', title: 'Partidos registrados' },
        { data: 'training_sessions_count', name: 'training_sessions_count', title: 'Sesiones entrenamiento' },
        { data: 'methodology_total', name: 'methodology_total', title: 'Metodologías total' },
        { data: 'methodology_planning_count', name: 'methodology_planning_count', title: 'Planeaciones' },
        { data: 'methodology_characterization_count', name: 'methodology_characterization_count', title: 'Caracterizaciones' },
        { data: 'methodology_monthly_count', name: 'methodology_monthly_count', title: 'Informes mensuales' },
        { data: 'methodology_category_monthly_count', name: 'methodology_category_monthly_count', title: 'Informes por categoría' },
    ]

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength: 10,
        processing: true,
        serverSide: true,
        searchDelay: 400,
        order: [[0, 'asc']],
        pipeline: { pages: 5 },
        ajax: async (data, callback) => {
            try {
                const response = await api.get('/api/v2/reports/instructors/activity', {
                    params: {
                        ...data,
                        ...normalizeFilters(filters),
                    },
                })

                callback(response.data)
            } catch {
                callback(emptyDataTableResponse(data.draw))
            }
        },
        columns,
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            {
                targets: '_all',
                className: 'dt-head-center dt-body-center',
            },
        ],
    }

    const excelUrl = computed(() => buildExportUrl('xlsx', filters))
    const pdfUrl = computed(() => buildExportUrl('pdf', filters))

    const search = () => {
        const dt = table.value?.table?.dt

        if (dt) {
            dt.clearPipeline().draw()
        }
    }

    const loadMetadata = async (initialFilters = {}) => {
        isLoading.value = true
        loadError.value = null

        try {
            const response = await api.get('/api/v2/reports/instructors/activity/metadata')

            years.value = normalizeOptions(response.data.years)
            months.value = normalizeOptions(response.data.months)
            instructors.value = normalizeOptions(response.data.instructors)

            filters.year = initialFilters.year
                ?? response.data.defaultYear
                ?? years.value[years.value.length - 1]?.value
                ?? new Date().getFullYear()
            filters.month = initialFilters.month
                ?? response.data.defaultMonth
                ?? months.value[0]?.value
                ?? new Date().getMonth() + 1
            filters.instructor_id = instructors.value.some((instructor) => instructor.value === initialFilters.instructor_id)
                ? initialFilters.instructor_id
                : null

            isReady.value = true
        } catch (error) {
            loadError.value = error.response?.data?.message || 'No fue posible cargar los filtros del informe.'
        } finally {
            isLoading.value = false
        }
    }

    onMounted(() => {
        loadMetadata({
            year: parseQueryNumber(route.query.year),
            month: parseQueryNumber(route.query.month),
            instructor_id: parseQueryNumber(route.query.instructor_id),
        })
    })

    return {
        columns,
        excelUrl,
        filters,
        instructors,
        isLoading,
        isReady,
        loadError,
        months,
        options,
        pdfUrl,
        search,
        table,
        years,
    }
}
