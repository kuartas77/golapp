import { defineStore } from "pinia";
import api from "@/utils/axios";

export const useAuthUser = defineStore('auth-user', {
    state: () => ({
        user: null,
    }),
    getters: {
        isAuthenticated: state => !!state.user,
        // getUser: state => state.user
    },
    actions: {
        clearState() {
            this.$reset()
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
            try {
                await api.post("/api/v2/login", credentials)
                await this.getUser()
            } catch (error) {
                console.log(error)
                throw error.response?.data?.message || "Error al iniciar sesión";
            }
        },
        async logout() {
            try {
                await api.post("/api/v2/logout");
            } finally {
                this.user = null;
            }
        }
    },
    persist: true,
})