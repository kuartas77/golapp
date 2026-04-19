import { defineStore } from "pinia";
import api from "@/utils/axios";

export const USER_CONTEXT_REFRESH_INTERVAL_MS = 60 * 1000;

let syncUserPromise = null;
let lastUserSyncAt = 0;

export const useAuthUser = defineStore('auth-user', {
    state: () => ({
        user: null,
        initialized: false,
        roles: [],
        permissions: [],
        schoolPermissions: {},
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
        },
        clearState() {
            this.$reset()
            lastUserSyncAt = 0
        },
        markContextStale() {
            lastUserSyncAt = 0
        },
        shouldRefreshContext(force = false) {
            if (force || !this.initialized || lastUserSyncAt === 0) {
                return true
            }

            return (Date.now() - lastUserSyncAt) >= USER_CONTEXT_REFRESH_INTERVAL_MS
        },
        async init(options = {}) {
            const force = Boolean(options?.force)
            const silent = Boolean(options?.silent)
            const preserveStateOnError = Boolean(options?.preserveStateOnError)

            if (!this.shouldRefreshContext(force)) {
                return this.isAuthenticated
            }

            if (syncUserPromise) {
                return syncUserPromise
            }

            syncUserPromise = (async () => {
                let didSyncUserContext = false

                try {
                    await this.getUser({ silent, preserveStateOnError })
                    didSyncUserContext = true
                } catch {
                    if (!preserveStateOnError) {
                        this.resetAuthContext()
                    }
                } finally {
                    this.initialized = true
                    if (didSyncUserContext) {
                        lastUserSyncAt = Date.now()
                    }
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
                    system_notify: data.data.system_notify,
                };
                this.roles = data.data.roles
                this.permissions = data.data.permissions || []
                this.schoolPermissions = data.data.school_permissions || {}
                lastUserSyncAt = Date.now()
                return this.user
            } catch {
                if (!options?.preserveStateOnError) {
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
        async logout() {
            try {
                await api.post("/api/v2/logout");
            } catch {}
            finally {
                this.resetAuthContext()
                this.initialized = true
                lastUserSyncAt = 0
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
