import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as any,
    isAuthenticated: false,
  }),
  getters: {
    isAdmin: (state) => state.user?.role === 'admin',
    userName: (state) => state.user?.name ?? '',
  },
  actions: {
    async login(email: string, password: string) {
      const config = useRuntimeConfig()
      const { data, error } = await useFetch(`${config.public.apiBase}/auth/login`, {
        method: 'POST',
        body: { email, password },
      })
      if (error.value) throw new Error(error.value.data?.message || 'ورود ناموفق')
      this.token = data.value.token
      this.user = data.value.user
      this.isAuthenticated = true
      if (process.client) {
        localStorage.setItem('token', data.value.token)
        localStorage.setItem('user', JSON.stringify(data.value.user))
      }
      return data.value
    },
    logout() {
      this.token = null
      this.user = null
      this.isAuthenticated = false
      if (process.client) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
    },
    initAuth() {
      if (process.client) {
        const token = localStorage.getItem('token')
        const user = localStorage.getItem('user')
        if (token && user) {
          this.token = token
          this.user = JSON.parse(user)
          this.isAuthenticated = true
        }
      }
    },
  },
})
