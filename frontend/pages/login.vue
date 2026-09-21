<script setup lang="ts">
definePageMeta({ layout: 'blank' })
const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await authStore.login(email.value, password.value)
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-bl from-primary-700 to-primary-900">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">TalentMatch</h1>
        <p class="text-gray-500 text-sm mt-1">سامانه هوشمند مدیریت استعداد</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="label">پست الکترونیکی</label>
          <input v-model="email" type="email" class="input" placeholder="your@email.com" required />
        </div>
        <div>
          <label class="label">رمز عبور</label>
          <input v-model="password" type="password" class="input" placeholder="••••••••" required />
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
          {{ error }}
        </div>

        <button type="submit" class="btn-primary w-full py-2.5" :disabled="loading">
          {{ loading ? 'در حال ورود...' : 'ورود به سیستم' }}
        </button>
      </form>
    </div>
  </div>
</template>