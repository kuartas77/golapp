import axios from "axios";

// Crear instancia
const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL, // cambia por tu URL
    timeout: 10000,
});

// Interceptor de request (antes de enviar la petición)
// api.interceptors.request.use(
//     (config) => {
//         // Ejemplo: añadir token a las cabeceras
//         const token = localStorage.getItem("token");
//         if (token) {
//             config.headers.Authorization = `Bearer ${token}`;
//         }
//         return config;
//     },
//     (error) => {
//         return Promise.reject(error);
//     }
// );

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
            localStorage.removeItem('user')
            localStorage.removeItem('token')
            localStorage.removeItem('refresh')
        }
        return Promise.reject(error);
    }
);

export default api;