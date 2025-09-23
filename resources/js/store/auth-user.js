import { defineStore } from "pinia";

export const useAuthUser = defineStore('auth-user', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
        refresh_token: localStorage.getItem('refresh') || null,
    }),
    getters: {
        isAuthenticated: state => !!state.token,
        getToken: state => state.token,
        getUser: state => state.user
    },
    actions: {
        setUser(user) {
            this.user = user
        },
        setToken(token) {
            this.token = token
        },
        setRefresh(token) {
            this.token = token
        },
        clearState () {
            this.$reset()
        },
        login({user, token, refresh}) {
            this.setUser(user)
            this.setToken(token)
            this.setRefresh(refresh)

            // Guardar en localStorage
            localStorage.setItem('user', JSON.stringify(user))
            localStorage.setItem('token', token)
            localStorage.setItem('refresh', refresh)
        },
        logout() {
            this.setUser(null)
            this.setToken(null)
            this.setRefresh(null)

            // Borrar de localStorage
            localStorage.removeItem('user')
            localStorage.removeItem('token')
            localStorage.removeItem('refresh')
        }
    }
})