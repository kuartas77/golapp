import { defineStore } from "pinia";
import api from "@/utils/axios";

const resolvePayload = (payload) => payload?.data ?? payload;

export const useGuardianAuth = defineStore('guardian-auth', {
    state: () => ({
        user: null,
        initialized: false,
    }),
    getters: {
        isAuthenticated: (state) => !!state.user,
    },
    actions: {
        clearState() {
            this.$reset();
        },
        setUser(user) {
            this.user = user;
            this.initialized = true;
        },
        async init() {
            if (this.initialized) {
                return this.isAuthenticated;
            }

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
            const response = await api.get('/api/v2/portal/acudientes/me', {
                skipAuthRedirect: true,
            });
            this.user = resolvePayload(response.data);
            return this.user;
        },
        async login(credentials) {
            await api.post('/api/v2/portal/acudientes/login', credentials);
            await this.getUser();
            this.initialized = true;
        },
        async logout() {
            try {
                await api.post('/api/v2/portal/acudientes/logout');
            } catch {
                //
            } finally {
                this.user = null;
                this.initialized = true;
            }
        },
        async forgotPassword(email) {
            return api.post('/api/v2/portal/acudientes/forgot-password', { email });
        },
        async resetPassword(payload) {
            return api.post('/api/v2/portal/acudientes/reset-password', payload);
        },
    },
});
