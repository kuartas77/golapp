const state = {
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    refresh_token: localStorage.getItem('refresh') || null,
}

const getters = {
    isAuthenticated: state => !!state.token,
    getToken: state => state.token,
    getUser: state => state.user
}

const mutations = {
    setUser(state, user) {
        state.user = user
    },
    setToken(state, token) {
        state.token = token
    },
    setRefresh(state, token) {
        state.token = token
    }
}

const actions = {
    login({ commit }, { user, token, refresh }) {
        commit('setUser', user)
        commit('setToken', token)
        commit('setRefresh', refresh)

        // Guardar en localStorage
        localStorage.setItem('user', JSON.stringify(user))
        localStorage.setItem('token', token)
        localStorage.setItem('refresh', refresh)
    },
    logout({ commit }) {
        commit('setUser', null)
        commit('setToken', null)
        commit('setRefresh', null)

        // Borrar de localStorage
        localStorage.removeItem('user')
        localStorage.removeItem('token')
        localStorage.removeItem('refresh')
    }
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};