import axios from "axios";
import { useAuthUser } from '@/store/auth-user'
import { useGuardianAuth } from '@/store/guardian-auth'
import router from "@/router";

// Crear instancia
const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL, // cambia por tu URL
    timeout: 10000,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
});

// Interceptor de request (antes de enviar la petición)
api.interceptors.request.use(
    async (config) => {
        // Agregar token CSRF para Laravel
        const csrfToken = document.querySelector('meta[name="csrf-token"]')
        if (csrfToken) {
            config.headers['X-CSRF-TOKEN'] = csrfToken.content
        }
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
        const guardianAuth = useGuardianAuth()

        const status = error.response?.status
        const skipAuthRedirect = Boolean(error.config?.skipAuthRedirect)
        const currentRoute = router.currentRoute.value
        const currentPath = String(currentRoute.path ?? '')
        const isGuardianArea = currentPath.startsWith('/portal/acudientes')
        const isPublicPortal = currentPath.startsWith('/portal') && !isGuardianArea

        // Manejo centralizado de errores
        if (status === 401 || status === 419) {
            if (skipAuthRedirect) {
                return Promise.reject(error);
            }

            if (isGuardianArea) {
                guardianAuth.clearState()

                if (!['guardian-login', 'guardian-reset-password'].includes(String(currentRoute.name ?? ''))) {
                    await router.push({
                        name: 'guardian-login',
                        query: currentPath !== '/portal/acudientes/login'
                            ? { redirect: currentRoute.fullPath }
                            : undefined,
                    })
                }

                return Promise.reject(error);
            }

            if (!isPublicPortal) {
                auth.clearState()

                if (router.currentRoute.value.name !== 'login') {
                    await router.push({ name: 'login' })
                }
            }
        }
        return Promise.reject(error);
    }
);

export default api;

// export default {
//     // Facturas
//     getInvoices(params) {
//         return apiClient.get('/invoices', { params })
//     },

//     getInvoice(id) {
//         return apiClient.get(`/invoices/${id}`)
//     },

//     createInvoice(data) {
//         return apiClient.post('/invoices', data)
//     },

//     deleteInvoice(id) {
//         return apiClient.delete(`/invoices/${id}`)
//     },

//     addPayment(invoiceId, data) {
//         return apiClient.post(`/invoices/${invoiceId}/payment`, data)
//     },

//     // Datos de inscripción
//     getInscriptionData(inscriptionId) {
//         return apiClient.get(`/inscriptions/${inscriptionId}/invoice-data`)
//     }
// }
