import axios from "axios";

// Crear instancia
const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL, // cambia por tu URL
    timeout: 10000,
});

await api.get("/sanctum/csrf-cookie");

api.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
api.defaults.withCredentials = true;

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
    (response) => {
        // Puedes transformar la respuesta si lo necesitas
        return response;
    },
    (error) => {
        // Manejo centralizado de errores
        if (error.response && error.response.status === 401) {
            console.warn("No autorizado, redirigiendo al login...");
            // Redirigir o limpiar sesión
        }
        return Promise.reject(error);
    }
);

export default api;