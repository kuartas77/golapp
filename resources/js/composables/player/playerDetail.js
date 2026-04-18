import dayjs from '@/utils/dayjs';
import { useSetting } from '@/store/settings-store';
import { getCurrentInstance, ref, onMounted, computed, useTemplateRef } from 'vue'
import * as yup from 'yup'
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { Spanish } from "flatpickr/dist/l10n/es.js"
import { useRoute, useRouter } from 'vue-router'

export default function usePlayerDetail() {
    const DEFAULT_GUARDIAN_RELATIONSHIP = '30'
    const transformEmptyToNull = (value, originalValue) => (originalValue === '' ? null : value)
    const nullableString = () => yup.string().nullable().transform(transformEmptyToNull)
    const requiredString = (message = 'Este campo es obligatorio.') => yup.string().required(message).transform(transformEmptyToNull)
    const requiredEmail = (requiredMessage = 'El correo electrónico es obligatorio.') =>
        yup.string().email('Ingresa un correo electrónico válido.').required(requiredMessage).transform(transformEmptyToNull)

    const personalSchemaShape = {
        photo: yup.mixed().nullable().transform(transformEmptyToNull),
        unique_code: requiredString('El código único es obligatorio.'),
        identification_document: requiredString('El documento de identidad es obligatorio.'),
        document_type: requiredString('El tipo de documento es obligatorio.'),
        date_birth: requiredString('La fecha de nacimiento es obligatoria.'),
        names: requiredString('Los nombres son obligatorios.'),
        last_names: requiredString('Los apellidos son obligatorios.'),
        place_birth: requiredString('El lugar de nacimiento es obligatorio.'),
        gender: requiredString('El género es obligatorio.'),
        rh: requiredString('El grupo sanguíneo es obligatorio.'),
    }

    const generalSchemaShape = {
        email: requiredEmail(),
        eps: requiredString('La entidad de salud es obligatoria.'),
        address: requiredString('La dirección de residencia es obligatoria.'),
        municipality: requiredString('El municipio de residencia es obligatorio.'),
        neighborhood: requiredString('El barrio de residencia es obligatorio.'),
        phones: requiredString('El número telefónico es obligatorio.'),
        school: requiredString('La institución educativa es obligatoria.'),
        degree: nullableString(),
        jornada: nullableString(),
        student_insurance: nullableString(),
        medical_history: nullableString(),
    }

    const familySchemaShape = {
        relationship_0: nullableString(),
        names_0: requiredString('Los nombres completos del acudiente son obligatorios.'),
        document_0: requiredString('El documento del acudiente es obligatorio.'),
        phone_0: requiredString('El WhatsApp o teléfono del acudiente es obligatorio.'),
        business_0: requiredString('La ocupación del acudiente es obligatoria.'),
    }

    const formSteps = [
        {
            title: 'Información personal',
            fields: {
                photo: 'Foto',
                unique_code: 'Código único',
                identification_document: 'Documento de identidad',
                document_type: 'Tipo de documento',
                date_birth: 'Fecha de nacimiento',
                names: 'Nombres',
                last_names: 'Apellidos',
                place_birth: 'Lugar de nacimiento',
                gender: 'Género',
                rh: 'Grupo sanguíneo',
            },
        },
        {
            title: 'Información general',
            fields: {
                email: 'Correo electrónico',
                eps: 'Entidad de salud',
                address: 'Dirección de residencia',
                municipality: 'Municipio de residencia',
                neighborhood: 'Barrio de residencia',
                phones: 'Teléfono o celular',
                school: 'Institución educativa',
                degree: 'Grado que cursa',
                jornada: 'Jornada de estudio',
                student_insurance: 'Seguro estudiantil',
                medical_history: 'Antecedentes médicos',
            },
        },
        {
            title: 'Información familiar',
            fields: {
                names_0: 'Nombres completos del acudiente',
                document_0: 'Documento de identidad del acudiente',
                phone_0: 'WhatsApp o teléfono del acudiente',
                business_0: 'Ocupación del acudiente',
            },
        },
    ]

    const fieldMeta = formSteps.reduce((accumulator, stepConfig, stepIndex) => {
        Object.entries(stepConfig.fields).forEach(([field, label]) => {
            accumulator[field] = {
                label,
                stepIndex,
                stepTitle: stepConfig.title,
            }
        })

        return accumulator
    }, {})

    const backendFieldAliases = {
        'people.0.relationship': 'relationship_0',
        'people.0.names': 'names_0',
        'people.0.identification_card': 'document_0',
        'people.0.phone': 'phone_0',
        'people.0.business': 'business_0',
    }

    const router = useRouter()
    // settings flatpick
    const flatpickrConfig = {
        locale: Spanish,
        minDate: dayjs().subtract(20, 'year').format('YYYY-M-D'),
        maxDate: dayjs().subtract(1, 'year').format('YYYY-M-D')
    }
    const globalError = ref(null)
    const formErrorSummary = ref([])
    const { proxy } = getCurrentInstance()
    const route = useRoute()
    const settings = useSetting()
    const step = ref(0)
    const degrees = Array.from({ length: 11 }, (_, i) => i + 1)
    const formPlayer = useTemplateRef('form-player')
    const guardianPortalEnabled = ref(false)
    const isLoading = ref(false)
    const loadingText = ref('')
    const currentTextPlayer = ref('')
    const initialValues = ref({
        photo: null,
        unique_code: null,
        identification_document: null,
        document_type: null,
        date_birth: null,
        names: null,
        last_names: null,
        place_birth: null,
        gender: null,
        rh: null,

        email: null,
        eps: null,
        address: null,
        municipality: null,
        neighborhood: null,
        phones: null,
        school: null,
        degree: null,
        jornada: null,
        student_insurance: null,
        medical_history: null,

        relationship_0: DEFAULT_GUARDIAN_RELATIONSHIP,
        names_0: null,
        document_0: null,
        phone_0: null,
        business_0: null,
    })

    const schemas = [
        yup.object(personalSchemaShape),
        yup.object(generalSchemaShape),
        yup.object(familySchemaShape),
    ]
    const fullSchema = yup.object({
        ...personalSchemaShape,
        ...generalSchemaShape,
        ...familySchemaShape,
    })

    // Computed: schema by step
    const schema = computed(() => schemas[step.value])
    const hasGeneralErrors = computed(() => Boolean(globalError.value) || formErrorSummary.value.length > 0)

    // Configuration wizard
    const wizardOptions = (validateFn) => ({
        transitionEffect: 1,
        onStepChanging: async (current, next) => {
            const result = await validateFn()
            return result.valid
        },
        onFinishing: async (current) => {
            const result = await validateFn()
            return result.valid
        },
    })

    const clearErrorState = () => {
        globalError.value = null
        formErrorSummary.value = []
    }

    const goToStep = (newStep) => {
        if (typeof newStep === 'number' && newStep >= 0) {
            step.value = newStep
        }
    }

    const normalizeBackendKey = (key) => backendFieldAliases[key] || key.replace(/\.(\d+)(?=\.|$)/g, "[$1]")

    const normalizeBackendErrors = (backendErrors = {}) => Object.keys(backendErrors).reduce((accumulator, key) => {
        accumulator[normalizeBackendKey(key)] = Array.isArray(backendErrors[key])
            ? backendErrors[key][0]
            : backendErrors[key]

        return accumulator
    }, {})

    const prettifyField = (field) => field
        .replace(/\[(\d+)\]/g, ' $1 ')
        .replace(/_/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()

    const buildErrorSummary = (errors = {}) => Object.entries(errors)
        .filter(([, message]) => Boolean(message))
        .map(([field, message]) => {
            const meta = fieldMeta[field]

            return {
                field,
                label: meta?.label || prettifyField(field),
                message,
                stepIndex: typeof meta?.stepIndex === 'number' ? meta.stepIndex : null,
                stepTitle: meta?.stepTitle || null,
            }
        })
        .sort((left, right) => {
            const leftStep = left.stepIndex ?? Number.MAX_SAFE_INTEGER
            const rightStep = right.stepIndex ?? Number.MAX_SAFE_INTEGER
            return leftStep - rightStep
        })

    const moveToFirstErrorStep = (summary = []) => {
        const firstErrorWithStep = summary.find((item) => item.stepIndex !== null)

        if (firstErrorWithStep) {
            step.value = firstErrorWithStep.stepIndex
        }
    }

    const escapeHtml = (value = '') => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')

    const showValidationAlert = async (summary = []) => {
        if (!summary.length || !proxy?.$swal) {
            return
        }

        const previewItems = summary.slice(0, 6).map((item) => {
            const stepTitle = item.stepTitle ? `${item.stepTitle}: ` : ''
            return `<li><strong>${escapeHtml(stepTitle + item.label)}</strong><br>${escapeHtml(item.message)}</li>`
        }).join('')

        const remainingErrors = summary.length - 6
        const remainingHtml = remainingErrors > 0
            ? `<li>Y ${remainingErrors} error(es) más en el formulario.</li>`
            : ''

        await proxy.$swal.fire({
            icon: 'error',
            title: 'Hay campos por corregir',
            html: `
                <div class="text-start">
                    <p class="mb-2">Te llevamos al primer paso con errores para que puedas corregirlos sin perder el contexto.</p>
                    <ul class="ps-3 mb-0">
                        ${previewItems}
                        ${remainingHtml}
                    </ul>
                </div>
            `,
            confirmButtonText: 'Revisar formulario',
        })
    }

    const registerFormErrors = async (errors, message) => {
        const summary = buildErrorSummary(errors)

        formErrorSummary.value = summary
        globalError.value = message

        moveToFirstErrorStep(summary)
        await showValidationAlert(summary)
    }

    const validateBeforeSubmit = async (values, setErrors) => {
        try {
            await fullSchema.validate(values, { abortEarly: false })
            return true
        } catch (error) {
            if (!(error instanceof yup.ValidationError)) {
                throw error
            }

            const formattedErrors = error.inner.reduce((accumulator, currentError) => {
                if (!currentError.path || accumulator[currentError.path]) {
                    return accumulator
                }

                accumulator[currentError.path] = currentError.message
                return accumulator
            }, {})

            if (!Object.keys(formattedErrors).length && error.path) {
                formattedErrors[error.path] = error.message
            }

            setErrors(formattedErrors)
            await registerFormErrors(
                formattedErrors,
                'Revisa los campos obligatorios. Algunos pertenecen a pasos anteriores del formulario.'
            )
            return false
        }
    }

    const onSubmit = async (values, { setErrors }) => {
        clearErrorState()
        setErrors({})

        try {
            const isFormValid = await validateBeforeSubmit(values, setErrors)

            if (!isFormValid) {
                return
            }

            isLoading.value = true
            loadingText.value = 'Guardando información del deportista...'
            const formData = new FormData();
            formData.append('_method', 'PUT')

            for (const key in values) {
                if (Object.prototype.hasOwnProperty.call(values, key)) {
                    const value = values[key]
                    if (value instanceof File) {
                        formData.append(key, value, value.name)
                    } else {
                        formData.append(key, value ?? '')
                    }
                }
            }

            const response = await api.post(`/api/v2/players/${route.params.unique_code}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })

            if (response.data.success) {
                showMessage('Guardado correctamente.')
                router.push({ name: 'players' })
            }

        } catch (error) {
            if (error.response?.status === 422) {
                const formattedErrors = normalizeBackendErrors(error.response.data?.errors || {})
                setErrors(formattedErrors)
                await registerFormErrors(
                    formattedErrors,
                    'Encontramos errores al guardar. Revisa el resumen y corrige los campos indicados.'
                )
                return
            }

            showMessage('Algo ha salido mal.', 'error')
            globalError.value = error.response?.data?.message || 'Error inesperado'
        } finally {
            isLoading.value = false
            loadingText.value = ''
        }
    }

    onMounted(async () => {
        currentTextPlayer.value = `Deportista ${route.params.unique_code}`
        usePageTitle(currentTextPlayer)
        onLoadData()
    })

    const onLoadData = async () => {
        try {
            clearErrorState()
            isLoading.value = true
            loadingText.value = 'Cargando información del deportista...'
            guardianPortalEnabled.value = false

            if (!settings.document_types.length || !settings.genders.length) {
                await settings.getSettings()
            }

            const response = await api.get(`/api/v2/players/${route.params.unique_code}/edit`)
            guardianPortalEnabled.value = Boolean(response.data.school_tutor_platform)

            formPlayer.value?.setValues({
                photo: response.data.photo_url,
                unique_code: response.data.unique_code,
                identification_document: response.data.identification_document,
                document_type: response.data.document_type,
                date_birth: response.data.date_birth,
                names: response.data.names,
                last_names: response.data.last_names,
                place_birth: response.data.place_birth,
                gender: response.data.gender,
                rh: response.data.rh,
                email: response.data.email,
                eps: response.data.eps,
                address: response.data.address,
                municipality: response.data.municipality,
                neighborhood: response.data.neighborhood,
                phones: response.data.phones,
                school: response.data.school,
                degree: response.data.degree,
                jornada: response.data.jornada,
                student_insurance: response.data.student_insurance,
                medical_history: response.data.medical_history,
                relationship_0: DEFAULT_GUARDIAN_RELATIONSHIP,
                names_0: null,
                document_0: null,
                phone_0: null,
                business_0: null,
            })

            if (Array.isArray(response.data.people) && response.data.people.length) {
                const guardian = response.data.people.find((element) =>
                    [1, '1', true, 'true'].includes(element.tutor)
                )
                    ?? response.data.people[0]

                formPlayer.value?.setValues({
                    relationship_0: guardian?.relationship ?? DEFAULT_GUARDIAN_RELATIONSHIP,
                    names_0: guardian?.names ?? null,
                    document_0: guardian?.identification_card ?? null,
                    phone_0: guardian?.phone ?? guardian?.mobile ?? null,
                    business_0: guardian?.business ?? null,
                })
            }
        } catch (error) {
            console.warn(error)
            globalError.value = error.response?.data?.message || 'No fue posible cargar la información del deportista.'
            showMessage('Error al cargar la información del deportista.', 'error')
        } finally {
            isLoading.value = false
            loadingText.value = ''
        }
    }

    return {
        onSubmit,
        currentTextPlayer,
        wizardOptions,
        step,
        initialValues,
        flatpickrConfig,
        settings,
        schema,
        degrees,
        loadingText,
        isLoading,
        guardianPortalEnabled,
        globalError,
        formErrorSummary,
        hasGeneralErrors,
        goToStep,
    }
}
