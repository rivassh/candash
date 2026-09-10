export default {
  namespaced: true,
  state: () => ({
    token: null,
    user: null,
    isAuthenticated: false,
  }),
  getters: {
    isAdmin: (state) => state.user?.role === 'admin',
    userName: (state) => state.user?.name ?? '',
  },
  actions: {
    async login({ commit }, { email, password }) {
      const config = useRuntimeConfig()
      const { data, error } = await useFetch(`${config.public.apiBase}/auth/login`, {
        method: 'POST',
        body: { email, password },
      })
      if (error.value) throw new Error(error.value.data?.message || 'ورود ناموفق')
      commit('SET_TOKEN', data.value.token)
      commit('SET_USER', data.value.user)
      if (process.client) {
        localStorage.setItem('token', data.value.token)
        localStorage.setItem('user', JSON.stringify(data.value.user))
      }
      return data.value
    },
    logout({ commit }) {
      commit('CLEAR_AUTH')
      if (process.client) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
    },
    initAuth({ commit, state }) {
      if (process.client) {
        const token = localStorage.getItem('token')
        const user = localStorage.getItem('user')
        if (token && user) {
          commit('SET_TOKEN', token)
          commit('SET_USER', JSON.parse(user))
        }
      }
    },
  },
  mutations: {
    SET_TOKEN(state, token) {
      state.token = token
      state.isAuthenticated = true
    },
    SET_USER(state, user) {
      state.user = user
    },
    CLEAR_AUTH(state) {
      state.token = null
      state.user = null
      state.isAuthenticated = false
    },
  },
}