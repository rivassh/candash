export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()
  await authStore.initAuth()

  if (!authStore.isAuthenticated && to.path !== '/') {
    return navigateTo('/')
  }
  if (authStore.isAuthenticated && to.path === '/') {
    return navigateTo('/dashboard')
  }
})