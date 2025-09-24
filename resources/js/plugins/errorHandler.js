export default {
    install(app) {
        app.config.globalProperties.$handleBackendErrors = (error, setErrors, setGlobalError = null) => {
            if (error.response && error.response.status === 422) {
                const backendErrors = error.response.data.errors;

                // Mapeamos los errores de Laravel a formato VeeValidate
                const formattedErrors = Object.keys(backendErrors).reduce((acc, key) => {
                    acc[key] = backendErrors[key][0]; // Solo tomamos el primer mensaje
                    return acc;
                }, {});

                setErrors(formattedErrors);
            } else if (setGlobalError) {
                // Para errores no relacionados con campos
                setGlobalError(error.response?.data?.message || "Error inesperado");
            } else {
                console.error("Error inesperado:", error);
            }
        };
    },
};