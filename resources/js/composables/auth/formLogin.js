import { ref } from 'vue'
import { useMeta } from "@/composables/use-meta";
import api from "@/utils/axios";
import { useRouter, useRoute } from 'vue-router'
import { useAuthUser } from '@/store/auth-user'
import * as yup from 'yup'

export default function useFormLogin() {
    const store = useAuthUser();
    const route = useRoute()
    const router = useRouter()
    const pwd_type = ref("password");
    const form = ref(null)

    useMeta({ title: "Ingresar" });

    const formData = ref({
        email: '',
        password: ''
    });

    const schema = yup.object().shape({
        email: yup.string().email().required(),
        password: yup.string().required().min(6),
    })

    const handleLogin = (values, { resetForm }) => {
        let credentials = { email: values.email, password: values.password }

        axios.get("/sanctum/csrf-cookie").then(() => {
            api.post("/api/login", credentials).then(response => {
                if (response.status === 200) {
                    store.login({
                        token: response.data.access_token,
                        user: response.data.user,
                        refresh: response.data.refresh_token
                    })
                    resetForm()
                    const redirect = route.query.redirect || "/inicio"
                    router.push(redirect);
                }
            })
        })
    }

    return { form, formData, schema, handleLogin, pwd_type }
}