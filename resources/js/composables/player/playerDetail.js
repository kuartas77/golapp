import dayjs from '@/utils/dayjs';
import { useSetting } from '@/store/settings-store';
import { useRoute } from 'vue-router'
import { getCurrentInstance, ref, onMounted, computed, useTemplateRef } from 'vue'
import * as yup from 'yup'
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { Spanish } from "flatpickr/dist/l10n/es.js"


export default function usePlayerDetail() {

    // settings flatpick
    const flatpickrConfig = {
        locale: Spanish,
        minDate: dayjs().subtract(20, 'year').format('YYYY-M-D'),
        maxDate: dayjs().subtract(1, 'year').endOf('year').format('YYYY-M-D')
    }
    const globalError = ref(null)
    const { proxy } = getCurrentInstance()
    const route = useRoute()
    const settings = useSetting()
    const step = ref(0)
    const degrees = Array.from({ length: 11 }, (_, i) => i + 1)
    const parients = ref([])
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
        yup.object({
            photo: yup.mixed().nullable().transform((value, original) => (original === '' ? null : value)),
            unique_code: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            identification_document: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            document_type: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            date_birth: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            names: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            last_names: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            place_birth: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            gender: yup.string().required().transform((value, original) => (original === '' ? null : value)),
            rh: yup.string().required().transform((value, original) => (original === '' ? null : value)),
        }),
        yup.object({
            email: yup.string().email().required().transform((value, original) => (original === '' ? null : value)),
            eps: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            address: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            municipality: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            neighborhood: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            phones: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            school: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            degree: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            jornada: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            student_insurance: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            medical_history: yup.string().nullable().transform((value, original) => (original === '' ? null : value))
        }),
        yup.object({
            relationship_0: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            names_0: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            document_0: yup.string().when('names_0', {
                is: (namesValue) => !namesValue || namesValue === null,
                then: (schema) => schema.notRequired(),
                otherwise: (schema) => schema.required(),
            }).nullable().transform((value, original) => (original === '' ? null : value)),
            phone_0: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            business_0: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),

            relationship_1: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            names_1: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            document_1: yup.string().when('names_1', {
                is: (namesValue) => !namesValue || namesValue === null,
                then: (schema) => schema.notRequired(),
                otherwise: (schema) => schema.required(),
            }).nullable().transform((value, original) => (original === '' ? null : value)),
            phone_1: yup.string().nullable().transform((value, original) => (original === '' ? null : value)),
            business_1: yup.string().nullable().transform((value, original) => (original === '' ? null : value))
        })
    ]

    // Computed: schema by step
    const schema = computed(() => schemas[step.value])

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

    const onSubmit = async (values, actions) => {
        try {
            isLoading.value = true
            const formData = new FormData();
            formData.append('_method', 'PUT')

            for (const key in values) {
                if (Object.prototype.hasOwnProperty.call(values, key)) {
                    const value = values[key]
                    if (value instanceof File) {
                        formData.append(key, value, value.name)
                    } else {
                        formData.append(key, value)
                    }
                }
            }

            api.post(`/api/v2/players/${route.params.unique_code}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(resp => {
                if (resp.data.success) {
                    showMessage('Guardado correctamente.')
                } else {
                    showMessage('Algo salió mal.', 'error')
                }
            })

        } catch (error) {
            showMessage('Algo ha salido mal.', 'error')
            proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
        } finally {
            isLoading.value = false
        }
    }

    onMounted(async () => {
        currentTextPlayer.value = `Deportista ${route.params.unique_code}`
        usePageTitle(currentTextPlayer)
        onLoadData()
    })

    const onLoadData = async () => {
        try {
            const response = await api.get(`/api/v2/players/${route.params.unique_code}/edit`)

            formPlayer.value.setValues({
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

            if (response.data.people) {
                let newElement = {}
                response.data.people.forEach((element, index) => {
                    newElement[`relationship_${index}`] = element.relationship
                    newElement[`relationship_${index}`] = element.relationship
                    newElement[`names_${index}`] = element.names
                    newElement[`document_${index}`] = element.identification_card
                    newElement[`phone_${index}`] = element.phone
                    newElement[`business_${index}`] = element.business
                });
                // parients.value = Array.from({ length: newElement.length }, (_, i) => i + 1)
                parients.value = [0, 1]

                formPlayer.value.setValues(newElement)
            }
        } catch (error) {
            console.warn(error)
        }
    }

    return { onSubmit, currentTextPlayer, wizardOptions, step, initialValues, flatpickrConfig, settings, schema, degrees, parients, loadingText, isLoading }
}