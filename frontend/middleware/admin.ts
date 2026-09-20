import { useAuthStore } from '@/stores/auth'

export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  authStore.initAuth()

  if (!authStore.isAuthenticated) return navigateTo('/')
  if (!authStore.isAdmin) return navigateTo('/dashboard')
})