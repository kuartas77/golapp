import { ref } from 'vue'
import { useMeta } from "@/composables/use-meta";
import api from "@/utils/axios";
import { useRouter, useRoute } from 'vue-router'
import { useAuthUser } from '@/store/auth-user'
import * as yup from 'yup'

export default function useFormLogin() {
    const storeAuth = useAuthUser();
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


    const handleLogin = async (values, { resetForm }) => {
        try {
            const redirect = route.query.redirect || "/inicio"

            let credentials = { email: values.email, password: values.password }
            await storeAuth.login(credentials)

            router.push(redirect);
        } catch (err) {
            throw err
        }

        resetForm()

    }

    return { form, formData, schema, handleLogin, pwd_type }
}