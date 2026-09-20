export const useApi = () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()
  const { showErrorModal } = useJobVisionError()

  const headers = computed(() => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    Authorization: authStore.token ? `Bearer ${authStore.token}` : '',
  }))

  const apiFetch = async <T>(path: string, options: RequestInit = {}): Promise<T> => {
    const url = `${config.public.apiBase}${path}`
    const res = await fetch(url, {
      ...options,
      headers: {
        ...headers.value,
        ...(options.headers || {}),
      },
    })
    if (!res.ok) {
      const body = await res.json().catch(() => ({}))
      const message = body.message || `خطای HTTP ${res.status}`

      if (res.status === 401) {
        showErrorModal(message, path, options)
      }

      throw new Error(message)
    }
    return res.json()
  }

  const get = <T>(path: string, params?: Record<string, string>) => {
    const qs = params ? '?' + new URLSearchParams(params).toString() : ''
    return apiFetch<T>(`${path}${qs}`)
  }

  const post = <T>(path: string, body?: unknown) =>
    apiFetch<T>(path, { method: 'POST', body: JSON.stringify(body) })

  const put = <T>(path: string, body?: unknown) =>
    apiFetch<T>(path, { method: 'PUT', body: JSON.stringify(body) })

  const patch = <T>(path: string, body?: unknown) =>
    apiFetch<T>(path, { method: 'PATCH', body: JSON.stringify(body) })

  const del = <T>(path: string) => apiFetch<T>(path, { method: 'DELETE' })

  return { get, post, put, patch, del, apiFetch }
}
