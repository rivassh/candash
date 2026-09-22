import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
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
      const loginBody = { email, password }
      const { data, error } = await useFetch(`${config.public.apiBase}/auth/login`, {
        method: 'POST',
        body: loginBody,
        credentials: 'include',
      })
      if (error.value) throw new Error(error.value.data?.message || 'ورود ناموفق')
      this.user = data.value.user
      this.isAuthenticated = true
      return data.value
    },
    async logout() {
      const config = useRuntimeConfig()
      if (process.client) {
        await fetch(`${config.public.apiBase}/auth/logout`, {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
        })
      }
      this.user = null
      this.isAuthenticated = false
    },
    async initAuth() {
      if (process.client) {
        const config = useRuntimeConfig()
        const { data, error } = await useFetch(`${config.public.apiBase}/auth/me`, {
          credentials: 'include',
        })
        if (!error.value && data.value) {
          this.user = data.value
          this.isAuthenticated = true
        }
      }
    },
  },
})
