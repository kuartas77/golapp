import { defineStore } from "pinia";
import api from "@/utils/axios";

export const useAuthUser = defineStore('auth-user', {
    state: () => ({
        user: null,
        initialized: false,
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
                this.user = data.data;
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
            } finally {
                this.clearState()
                this.user = null;
                this.initialized = true
            }
        }
    },
    persist: true,
})