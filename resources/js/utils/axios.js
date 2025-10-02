import axios from "axios";
import { useAuthUser } from '@/store/auth-user'
import router from "@/router";

// Crear instancia
const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL, // cambia por tu URL
    timeout: 10000,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
});

// Interceptor de request (antes de enviar la petición)
api.interceptors.request.use(
    async (config) => {
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Interceptor de response (cuando llega la respuesta)
api.interceptors.response.use(
    response => response,
    async (error) => {
        const auth = useAuthUser()

        const status = error.response?.status
        // Manejo centralizado de errores
        if (status === 401 || status === 419) {
            auth.clearState()

            if (router.currentRoute.value.name !== 'login') {
                await router.push({ name: 'login' })
            }
        }
        return Promise.reject(error);
    }
);

export default api;