export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  authStore.initAuth()

  if (!authStore.isAuthenticated && to.path !== '/') {
    return navigateTo('/')
  }
  if (authStore.isAuthenticated && to.path === '/') {
    return navigateTo('/dashboard')
  }
})