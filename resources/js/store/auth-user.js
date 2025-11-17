import { defineStore } from "pinia";
import api from "@/utils/axios";

export const useAuthUser = defineStore('auth-user', {
    state: () => ({
        user: null,
        initialized: false,
        roles: [],
        permissions: [],
    }),
    getters: {
        isAuthenticated: state => !!state.user,
    },
    actions: {
        clearState() {
            this.$reset()
        },
        async init() {
            if (this.initialized) return this.isAuthenticated;
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
                const { data } = await api.get("/api/v2/user");
                this.user = {
                    name: data.data.name,
                    email: data.data.email,
                    school_name: data.data.school_name,
                    school_slug: data.data.school_slug,
                    school_logo: data.data.school_logo,
                };
                this.roles = data.data.roles
                this.permissions = data.data.permissions || []
            } catch {
                this.user = null;
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
                this.initialized = true
            }
        },
        can(permission) {
            return this.permissions.includes(permission)
        },

        canAny(permissions) {
            return permissions.some(p => this.can(p))
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