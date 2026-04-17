import { defineStore } from "pinia";
import api from "@/utils/axios";

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
        clearState() {
            this.$reset()
        },
        async init() {
            if (this.initialized && this.user?.system_notify !== undefined) return this.isAuthenticated;
            try {
                await this.getUser();
            } catch {
                this.user = null;
            } finally {
                this.initialized = true;
            }

            return this.isAuthenticated;
        },
        async getUser() {
            try {
                const { data } = await api.get("/api/v2/user", {
                    skipAuthRedirect: true,
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
            } catch {
                this.user = null;
                this.roles = []
                this.permissions = []
                this.schoolPermissions = {}
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
                this.user = null;
                this.roles = []
                this.permissions = []
                this.schoolPermissions = {}
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
