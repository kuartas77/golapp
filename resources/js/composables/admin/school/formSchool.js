import { routeName } from '@/composables/routeName';
import { ref, onMounted } from 'vue'
import * as yup from 'yup'
import axios from 'axios';
import useAlerts from '@/composables/alerts'

export default function useFormSchool() {
    const { toastSuccess, toastError, } = useAlerts()
    const form = ref(null)

    const formData = ref({
        id: '',
        slug: '',
        name: '',
        email: '',
        agent: '',
        address: '',
        phone: '',
        logo: null,
        create_contract: false,
        send_documents: false,
        tutor_platform: false,
        sign_player: false,
        inscriptions_enabled: false,
        NOTIFY_PAYMENT_DAY: '',
        INSCRIPTION_AMOUNT: 0,
        MONTHLY_PAYMENT: 0,
        ANNUITY: 0,
    })

    const schema = yup.object().shape({
        name: yup.string().required().min(6),
        email: yup.string().email().required(),
        agent: yup.string().required(),
        address: yup.string().required(),
        phone: yup.string().required(),
        NOTIFY_PAYMENT_DAY: yup.number().min(1).max(31).required(),
        INSCRIPTION_AMOUNT: yup.string().required(),
        MONTHLY_PAYMENT: yup.string().required(),
        ANNUITY: yup.string().required(),
        logo: yup.mixed(),
        // create_contract: yup.boolean().oneOf([true]),
        // send_documents: yup.boolean().oneOf([true]),
        // tutor_platform: yup.boolean().oneOf([true]),
        // sign_player: yup.boolean().oneOf([true]),
        // inscriptions_enabled: yup.boolean().oneOf([true]),
    })

    onMounted(async () => {
        const response = await axios.get('/v1/admin/school')
        let data = {
            id: response.data.id,
            slug: response.data.slug,
            name: response.data.name,
            email: response.data.email,
            agent: response.data.agent,
            address: response.data.address,
            phone: response.data.phone,
            logo: response.data.logo_file,
            create_contract: response.data.create_contract,
            send_documents: response.data.send_documents,
            tutor_platform: response.data.tutor_platform,
            sign_player: response.data.sign_player,
            inscriptions_enabled: response.data.inscriptions_enabled,
            NOTIFY_PAYMENT_DAY: response.data.settings.NOTIFY_PAYMENT_DAY,
            INSCRIPTION_AMOUNT: Number(response.data.settings.INSCRIPTION_AMOUNT),
            MONTHLY_PAYMENT: Number(response.data.settings.MONTHLY_PAYMENT),
            ANNUITY: Number(response.data.settings.ANNUITY),
        }

        form.value.resetForm()
        form.value.setValues(data)
    });

    onMounted(()=> {
        routeName()
    })

    const submit = (values) => {

        Swal.fire({
            title: "¿Quieres guardar los cambios?",
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Sí",
            denyButtonText: `No`,
        }).then((result) => {
            if (result.isConfirmed) {
                sendRequest(values)
            } else if (result.isDenied) {
                toastSuccess('Cancelado correctamente.')
            }
        });
    }

    const sendRequest = (values) => {
        let data = values
        data._method = 'PUT'
        axios.post(`/v1/admin/school/${values.slug}`, data).then(resp => {
            if(resp.data.success){
                toastSuccess()
            }else {
                toastError()
            }
        })
    }

    const reset = () => {
        form.value.resetForm()
    }

    return {
        form, formData, schema, submit, reset
    }
}
