import { getCurrentInstance, ref, useTemplateRef, onMounted } from 'vue'
import { useMeta } from "@/composables/use-meta"
import { useRouter, useRoute } from 'vue-router'
import { useAuthUser } from '@/store/auth-user'
import * as yup from 'yup'
import api from '@/utils/axios'

const DEFAULT_LOGIN_REDIRECT = '/inicio'

export const resolvePostLoginRedirect = (router, redirect) => {
    if (typeof redirect !== 'string' || !redirect.startsWith('/') || redirect.startsWith('//')) {
        return DEFAULT_LOGIN_REDIRECT
    }

    try {
        const target = router.resolve(redirect)
        const returnsToAuthScreen = target.matched.some(routeRecord => routeRecord.meta?.guest)

        if (target.matched.length === 0 || returnsToAuthScreen) {
            return DEFAULT_LOGIN_REDIRECT
        }

        return target.fullPath
    } catch {
        return DEFAULT_LOGIN_REDIRECT
    }
}


export default function useFormLogin() {
    const storeAuth = useAuthUser()
    const route = useRoute()
    const router = useRouter()
    const pwd_type = ref("password")
    const form = useTemplateRef('form')
    const { proxy } = getCurrentInstance()
    const globalError = ref(null)

    useMeta({ title: "Ingresar" })

    const formData = ref({
        email: typeof route.query.email === 'string' ? route.query.email : '',
        password: ''
    });

    const schema = yup.object().shape({
        email: yup.string().email().required(),
        password: yup.string().min(6).required(),
    })

    const handleLogin = async (values, actions) => {
        try {
            const redirect = resolvePostLoginRedirect(router, route.query.redirect)
            let credentials = { email: values.email, password: values.password }
            await storeAuth.login(credentials)
            await router.replace(redirect)
        } catch (error) {
            proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
        }
    }

    onMounted(async() => {
        await api.get("/sanctum/csrf-cookie")
    })

    return { form, formData, schema, handleLogin, pwd_type, globalError }
}
