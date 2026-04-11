import dayjs from '@/utils/dayjs';
import { useSetting } from '@/store/settings-store';
import { getCurrentInstance, ref, onMounted, computed, useTemplateRef } from 'vue'
import * as yup from 'yup'
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { Spanish } from "flatpickr/dist/l10n/es.js"
import { useRoute, useRouter } from 'vue-router'

export default function usePlayerDetail() {
    const defaultParients = [0, 1]
    const transformEmptyToNull = (value, originalValue) => (originalValue === '' ? null : value)
    const nullableString = () => yup.string().nullable().transform(transformEmptyToNull)
    const requiredString = (message = 'Este campo es obligatorio.') => yup.string().required(message).transform(transformEmptyToNull)
    const requiredEmail = (requiredMessage = 'El correo electrónico es obligatorio.') =>
        yup.string().email('Ingresa un correo electrónico válido.').required(requiredMessage).transform(transformEmptyToNull)
    const relationshipLabelByIndex = (index, label) => `${label} acudiente ${index + 1}`

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
        names_0: nullableString(),
        document_0: nullableString().when('names_0', {
            is: (namesValue) => !namesValue || namesValue === null,
            then: (schema) => schema.notRequired(),
            otherwise: (schema) => schema.required('El documento del acudiente 1 es obligatorio.'),
        }),
        phone_0: nullableString(),
        business_0: nullableString(),

        relationship_1: nullableString(),
        names_1: nullableString(),
        document_1: nullableString().when('names_1', {
            is: (namesValue) => !namesValue || namesValue === null,
            then: (schema) => schema.notRequired(),
            otherwise: (schema) => schema.required('El documento del acudiente 2 es obligatorio.'),
        }),
        phone_1: nullableString(),
        business_1: nullableString(),
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
            fields: defaultParients.reduce((accumulator, index) => ({
                ...accumulator,
                [`relationship_${index}`]: relationshipLabelByIndex(index, 'Parentesco'),
                [`names_${index}`]: relationshipLabelByIndex(index, 'Nombres completos'),
                [`document_${index}`]: relationshipLabelByIndex(index, 'Documento de identidad'),
                [`phone_${index}`]: relationshipLabelByIndex(index, 'WhatsApp o teléfono'),
                [`business_${index}`]: relationshipLabelByIndex(index, 'Ocupación'),
            }), {}),
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

    const backendFieldAliases = defaultParients.reduce((accumulator, index) => {
        accumulator[`people.${index}.relationship`] = `relationship_${index}`
        accumulator[`people.${index}.names`] = `names_${index}`
        accumulator[`people.${index}.identification_card`] = `document_${index}`
        accumulator[`people.${index}.phone`] = `phone_${index}`
        accumulator[`people.${index}.business`] = `business_${index}`
        return accumulator
    }, {})

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
    const parients = ref([...defaultParients])
    const formPlayer = useTemplateRef('form-player')
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

        relationship_0: null,
        names_0: null,
        document_0: null,
        phone_0: null,
        business_0: null,

        relationship_1: null,
        names_1: null,
        document_1: null,
        phone_1: null,
        business_1: null,
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

            if (!settings.document_types.length || !settings.relationships.length || !settings.genders.length) {
                await settings.getSettings()
            }

            const response = await api.get(`/api/v2/players/${route.params.unique_code}/edit`)

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
            })

            if (Array.isArray(response.data.people) && response.data.people.length) {
                const newElement = {}

                response.data.people.slice(0, defaultParients.length).forEach((element, index) => {
                    newElement[`relationship_${index}`] = element.relationship
                    newElement[`names_${index}`] = element.names
                    newElement[`document_${index}`] = element.identification_card
                    newElement[`phone_${index}`] = element.phone
                    newElement[`business_${index}`] = element.business
                })

                formPlayer.value?.setValues(newElement)
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
        parients,
        loadingText,
        isLoading,
        globalError,
        formErrorSummary,
        hasGeneralErrors,
        goToStep,
    }
}
