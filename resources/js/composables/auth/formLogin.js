import { getCurrentInstance, ref, onMounted } from 'vue'
import { useMeta } from "@/composables/use-meta"
import { useRouter, useRoute } from 'vue-router'
import { useAuthUser } from '@/store/auth-user'
import * as yup from 'yup'
import api from '@/utils/axios'


export default function useFormLogin() {
    const storeAuth = useAuthUser()
    const route = useRoute()
    const router = useRouter()
    const pwd_type = ref("password")
    const form = ref(null)
    const { proxy } = getCurrentInstance()
    const globalError = ref(null)

    useMeta({ title: "Ingresar" })

    const formData = ref({
        email: '',
        password: ''
    });

    const schema = yup.object().shape({
        email: yup.string().email().required(),
        password: yup.string().required().min(6),
    })

    const handleLogin = async (values, actions) => {
        try {
            const redirect = route.query.redirect || "/inicio"
            let credentials = { email: values.email, password: values.password }
            await storeAuth.login(credentials)
            router.push(redirect)
        } catch (error) {
            proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
        }
    }

    onMounted(async() => {
        await api.get("/sanctum/csrf-cookie")
    })

    return { form, formData, schema, handleLogin, pwd_type, globalError }
}