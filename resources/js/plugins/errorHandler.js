export default {
    install(app) {
        app.config.globalProperties.$handleBackendErrors = (error, setErrors, setGlobalError = null) => {
            if (error.response?.status === 422) {
                const backendErrors = error.response.data?.errors || {};

                const formattedErrors = Object.keys(backendErrors).reduce((acc, key) => {
                    // Convierte skill_controls.0.position -> skill_controls[0].position
                    const veeKey = key.replace(/\.(\d+)(?=\.|$)/g, "[$1]");

                    acc[veeKey] = Array.isArray(backendErrors[key])
                        ? backendErrors[key][0]
                        : backendErrors[key];

                    return acc;
                }, {});

                setErrors(formattedErrors);
                return;
            }

            if (setGlobalError) {
                setGlobalError(error.response?.data?.message || "Error inesperado");
                return;
            }
            console.error("Error inesperado:", error);
        };
    },
};