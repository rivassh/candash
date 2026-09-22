import { useAuthStore } from '@/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()
  await authStore.initAuth()

  if (!authStore.isAuthenticated) return navigateTo('/')
  if (!authStore.isAdmin) return navigateTo('/dashboard')
})