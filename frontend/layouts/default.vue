<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

const nav = [
  { label: 'داشبورد', icon: '📊', to: '/dashboard' },
  { label: 'موقعیت‌های شغلی', icon: '💼', to: '/positions' },
  { label: 'کاندیداها', icon: '👤', to: '/candidates' },
  { label: 'تطبیق', icon: '🔗', to: '/matches' },
  { label: 'مهارت‌ها', icon: '🎯', to: '/skills' },
  { label: 'گزارش‌ها', icon: '📋', to: '/audit' },
]

const route = useRoute()
const authStore = useAuthStore()
const router = useRouter()

async function logout() {
  await authStore.logout()
  router.push('/')
}
</script>

<template>
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-l border-gray-200 flex flex-col">
      <div class="p-5 border-b border-gray-100">
        <h1 class="text-lg font-bold text-primary-700">TalentMatch</h1>
        <p class="text-xs text-gray-400 mt-0.5">سامانه مدیریت استعداد</p>
      </div>

      <nav class="flex-1 p-3 space-y-1">
        <NuxtLink
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="nav-link"
          :class="{ 'nav-link-active': route.path.startsWith(item.to) }"
        >
          <span>{{ item.icon }}</span>
          <span>{{ item.label }}</span>
        </NuxtLink>
      </nav>

      <div class="p-4 border-t border-gray-100">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-sm font-bold">
            {{ authStore.userName.charAt(0) }}
          </div>
          <div>
            <p class="text-sm font-medium text-gray-800">{{ authStore.userName }}</p>
            <p class="text-xs text-gray-400">{{ authStore.isAdmin ? 'مدیر' : 'کارشناس HR' }}</p>
          </div>
        </div>
        <button @click="logout" class="btn-secondary w-full text-xs">
          خروج
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-auto">
      <slot />
    </main>
  </div>
</template>