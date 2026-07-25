import { defineStore } from "pinia";
import api from "@/utils/axios";

let syncUserPromise = null;

export const useAuthUser = defineStore('auth-user', {
    state: () => ({
        user: null,
        initialized: false,
        roles: [],
        permissions: [],
        schoolPermissions: {},
        schoolPermissionsConfigured: {},
        enabledSports: [],
    }),
    getters: {
        isAuthenticated: state => !!state.user,
        hasSystemNotify: state => Boolean(state.schoolPermissions?.['school.feature.system_notify']),
    },
    actions: {
        resetAuthContext() {
            this.user = null
            this.roles = []
            this.permissions = []
            this.schoolPermissions = {}
            this.schoolPermissionsConfigured = {}
            this.enabledSports = []
        },
        clearState() {
            this.$reset()
        },
        markContextStale() {
            this.initialized = false
        },
        async init(options = {}) {
            const force = Boolean(options?.force)
            const silent = Boolean(options?.silent)
            const preserveStateOnError = Boolean(options?.preserveStateOnError)

            if (!force && this.initialized) {
                return this.isAuthenticated
            }

            if (syncUserPromise) {
                return syncUserPromise
            }

            syncUserPromise = (async () => {
                try {
                    await this.getUser({ silent, preserveStateOnError })
                } catch {
                    if (!preserveStateOnError) {
                        this.resetAuthContext()
                    }
                } finally {
                    this.initialized = true
                    syncUserPromise = null
                }

                return this.isAuthenticated
            })()

            return syncUserPromise
        },
        async getUser(options = {}) {
            try {
                const { data } = await api.get("/api/v2/user", {
                    skipAuthRedirect: true,
                    skipGlobalLoader: Boolean(options?.silent),
                });
                this.user = {
                    id: data.data.id,
                    name: data.data.name,
                    email: data.data.email,
                    school_id: data.data.school_id,
                    school_name: data.data.school_name,
                    school_slug: data.data.school_slug,
                    school_logo: data.data.school_logo,
                    school_organization_type: data.data.school_organization_type,
                    enabled_sports: data.data.enabled_sports || [],
                    system_notify: data.data.system_notify,
                };
                this.roles = data.data.roles
                this.permissions = data.data.permissions || []
                this.schoolPermissions = data.data.school_permissions || {}
                this.schoolPermissionsConfigured = data.data.school_permissions_configured || {}
                this.enabledSports = data.data.enabled_sports || []
                return this.user
            } catch (error) {
                const status = error.response?.status
                const shouldPreserveState = Boolean(options?.preserveStateOnError) && ![401, 419].includes(status)

                if (!shouldPreserveState) {
                    this.resetAuthContext()
                }
                throw new Error('Unable to fetch authenticated user context')
            }
        },
        async login(credentials) {
            await api.post("/api/v2/login", credentials)
            await this.getUser()
            this.initialized = true;
        },
        async forgotPassword(email) {
            return api.post('/api/v2/forgot-password', { email })
        },
        async resetPassword(payload) {
            return api.post('/api/v2/reset-password', payload)
        },
        async logout() {
            try {
                await api.post("/api/v2/logout");
            } catch {}
            finally {
                this.resetAuthContext()
                this.initialized = true
            }
        },
        can(permission) {
            return this.permissions.includes(permission)
        },

        canAny(permissions) {
            return permissions.some(p => this.can(p))
        },

        hasSchoolPermission(permission) {
            return Boolean(this.schoolPermissions?.[permission])
        },

        hasAnySchoolPermission(permissions) {
            return permissions.some((permission) => this.hasSchoolPermission(permission))
        },

        hasRole(role) {
            return this.roles.includes(role)
        },

        hasAnyRole(roles) {
            return this.roles.some(p => this.hasRole(p))
        }
    },
    persist: true,
})
