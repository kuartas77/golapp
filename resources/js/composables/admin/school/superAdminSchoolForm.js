import { computed, getCurrentInstance, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import * as yup from 'yup'
import api from '@/utils/axios'

const defaultValues = () => ({
    name: '',
    address: '',
    phone: '',
    agent: '',
    email: '',
    is_enable: '0',
    logo: null,
    is_campus: false,
    multiple_schools: [],
})

export default function useSuperAdminSchoolForm(mode = 'create') {
    const form = ref(null)
    const schoolOptions = ref([])
    const initialValues = ref(defaultValues())
    const isLoading = ref(mode === 'edit')
    const isSaving = ref(false)
    const globalError = ref('')

    const route = useRoute()
    const router = useRouter()
    const { proxy } = getCurrentInstance()

    const isEditMode = computed(() => mode === 'edit')
    const title = computed(() => (isEditMode.value ? 'Editar escuela' : 'Crear escuela'))
    const description = computed(() => (
        isEditMode.value
            ? 'Actualiza la información administrativa, el estado y el grupo de sedes de la escuela.'
            : 'Registra una nueva escuela, define su administrador y configura si compartirá sedes.'
    ))
    const submitLabel = computed(() => (
        isSaving.value
            ? (isEditMode.value ? 'Guardando...' : 'Creando...')
            : (isEditMode.value ? 'Guardar cambios' : 'Crear escuela')
    ))

    const schema = yup.object({
        name: yup.string().required().min(3),
        address: yup.string().required(),
        phone: yup.string().required(),
        agent: yup.string().required(),
        email: yup.string().email().required(),
        is_enable: yup.string().required().oneOf(['0', '1']),
        logo: yup.mixed().nullable(),
        is_campus: yup.boolean().default(false),
        multiple_schools: yup.array()
            .of(yup.number().integer())
            .when('is_campus', {
                is: true,
                then: (currentSchema) => currentSchema.min(1, 'Debes seleccionar al menos una escuela.'),
                otherwise: (currentSchema) => currentSchema.default([]),
            }),
    })

    const resetForm = (values) => {
        initialValues.value = values

        if (form.value?.resetForm) {
            form.value.resetForm({ values })
        }
    }

    const normalizeValues = (payload) => ({
        name: payload.name ?? '',
        address: payload.address ?? '',
        phone: payload.phone ?? '',
        agent: payload.agent ?? '',
        email: payload.email ?? '',
        is_enable: payload.is_enable ? '1' : '0',
        logo: payload.logo_file ?? null,
        is_campus: Boolean(payload.is_campus),
        multiple_schools: Array.isArray(payload.multiple_schools) ? payload.multiple_schools : [],
    })

    const loadCreateOptions = async () => {
        const { data } = await api.get('/api/v2/admin/schools/options')

        schoolOptions.value = data.schools ?? []
        resetForm(defaultValues())
    }

    const loadSchool = async () => {
        const { data } = await api.get(`/api/v2/admin/schools/${route.params.slug}`)

        schoolOptions.value = data.schools ?? []
        resetForm(normalizeValues({
            ...(data.school ?? {}),
            multiple_schools: data.multiple_schools ?? [],
        }))
    }

    const buildPayload = (values) => {
        const payload = new FormData()

        Object.entries(values).forEach(([key, value]) => {
            if (key === 'multiple_schools') {
                ;(Array.isArray(value) ? value : []).forEach((schoolId) => {
                    payload.append('multiple_schools[]', String(schoolId))
                })

                return
            }

            if (value instanceof File) {
                payload.append(key, value, value.name)
                return
            }

            if (typeof value === 'boolean') {
                payload.append(key, value ? '1' : '0')
                return
            }

            if (value !== null && value !== undefined) {
                payload.append(key, value)
            }
        })

        return payload
    }

    const onCancel = () => {
        router.push({ name: 'schools' })
    }

    const submit = async (values, actions) => {
        globalError.value = ''
        isSaving.value = true

        try {
            const payload = buildPayload(values)
            const endpoint = isEditMode.value
                ? `/api/v2/admin/schools/${route.params.slug}`
                : '/api/v2/admin/schools'

            if (isEditMode.value) {
                payload.append('_method', 'PUT')
            }

            const { data } = await api.post(endpoint, payload)

            showMessage(data.message || 'Escuela guardada correctamente.')

            if (isEditMode.value) {
                await loadSchool()
            } else {
                await router.push({ name: 'schools' })
            }
        } catch (error) {
            proxy.$handleBackendErrors(error, actions.setErrors, (message) => {
                globalError.value = message
            })

            if (error.response?.status !== 422) {
                showMessage(
                    error.response?.data?.message || 'No fue posible guardar la escuela.',
                    'error'
                )
            }
        } finally {
            isSaving.value = false
        }
    }

    onMounted(async () => {
        isLoading.value = true

        try {
            if (isEditMode.value) {
                await loadSchool()
            } else {
                await loadCreateOptions()
            }
        } catch (error) {
            globalError.value = error.response?.data?.message || 'No fue posible cargar el formulario de escuela.'
            showMessage(globalError.value, 'error')
        } finally {
            isLoading.value = false
        }
    })

    return {
        description,
        form,
        globalError,
        initialValues,
        isEditMode,
        isLoading,
        isSaving,
        onCancel,
        schoolOptions,
        schema,
        submit,
        submitLabel,
        title,
    }
}
