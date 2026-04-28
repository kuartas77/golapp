import axios from "axios";
import { useAuthUser } from '@/store/auth-user'
import { useGuardianAuth } from '@/store/guardian-auth'
import { useAppState } from '@/store/app-state'
import { canAccessRoute } from '@/utils/routeAccess'
import router from "@/router";

// Crear instancia
const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL, // cambia por tu URL
    timeout: 10000,
    withCredentials: true,
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
});

let csrfRefreshPromise = null

const shouldTrackGlobalLoader = (config) => Boolean(config) && !Boolean(config.skipGlobalLoader)

const refreshCsrfCookie = async () => {
    if (!csrfRefreshPromise) {
        csrfRefreshPromise = api.get('/sanctum/csrf-cookie', {
            skipAuthRedirect: true,
            skipCsrfRetry: true,
            skipGlobalLoader: true,
        }).finally(() => {
            csrfRefreshPromise = null
        })
    }

    return csrfRefreshPromise
}

// Interceptor de request (antes de enviar la petición)
api.interceptors.request.use(
    async (config) => {
        const appState = useAppState()

        if (shouldTrackGlobalLoader(config)) {
            appState.startGlobalLoading()
        }

        if (config.headers) {
            if (typeof config.headers.delete === 'function') {
                config.headers.delete('X-CSRF-TOKEN')
                config.headers.delete('x-csrf-token')
            } else {
                delete config.headers['X-CSRF-TOKEN']
                delete config.headers['x-csrf-token']
            }
        }

        if (typeof FormData !== 'undefined' && config.data instanceof FormData) {
            if (typeof config.headers?.setContentType === 'function') {
                config.headers.setContentType(undefined)
            } else if (config.headers) {
                delete config.headers['Content-Type']
                delete config.headers['content-type']
            }
        }

        return config;
    },
    (error) => {
        const appState = useAppState()

        if (shouldTrackGlobalLoader(error.config)) {
            appState.stopGlobalLoading()
        }

        return Promise.reject(error);
    }
);

// Interceptor de response (cuando llega la respuesta)
api.interceptors.response.use(
    (response) => {
        const appState = useAppState()

        if (shouldTrackGlobalLoader(response.config)) {
            appState.stopGlobalLoading()
        }

        return response
    },
    async (error) => {
        const auth = useAuthUser()
        const guardianAuth = useGuardianAuth()
        const appState = useAppState()

        const status = error.response?.status
        const originalRequest = error.config ?? {}
        const skipAuthRedirect = Boolean(error.config?.skipAuthRedirect)
        const skipCsrfRetry = Boolean(error.config?.skipCsrfRetry)
        const currentRoute = router.currentRoute.value
        const currentPath = String(currentRoute.path ?? '')
        const isGuardianArea = currentPath.startsWith('/portal/acudientes')
        const isPublicPortal = currentPath.startsWith('/portal') && !isGuardianArea

        if (shouldTrackGlobalLoader(error.config)) {
            appState.stopGlobalLoading()
        }

        if (
            status === 419
            && !skipCsrfRetry
            && !originalRequest.__isRetryAfterCsrf
            && !String(originalRequest.url ?? '').includes('/sanctum/csrf-cookie')
        ) {
            await refreshCsrfCookie()

            return api.request({
                ...originalRequest,
                __isRetryAfterCsrf: true,
                skipAuthRedirect,
                skipCsrfRetry: true,
            })
        }

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

                if (!['login', 'forgot-password', 'reset-password'].includes(String(router.currentRoute.value.name ?? ''))) {
                    await router.push({ name: 'login' })
                }
            }
        }

        if (status === 403 && !skipAuthRedirect && !isGuardianArea && !isPublicPortal) {
            auth.markContextStale()
            await auth.init({ force: true, silent: true, preserveStateOnError: true })

            if (auth.isAuthenticated && !canAccessRoute(router.currentRoute.value, auth)) {
                await router.push({ name: 'dashboard' })
            }
        }

        return Promise.reject(error);
    }
);

export default api;
