<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from '#app'
import { useAuthStore } from '@/stores/auth'

definePageMeta({ layout: 'default' })

const nav = [
  { label: 'داشبورد', icon: '📊', to: '/dashboard' },
  { label: ' موقعیت‌های شغلی', icon: '💼', to: '/positions' },
  { label: 'کاندیداها', icon: '👤', to: '/candidates' },
  { label: 'تطبیق', icon: '🔗', to: '/matches' },
  { label: 'مهارت‌ها', icon: '🎯', to: '/skills' },
  { label: 'گزارش‌ها', icon: '📋', to: '/audit' },
]

const route = useRoute()
const authStore = useAuthStore()
const router = useRouter()
const sidebarOpen = ref(false)
const isMobile = ref(false)

const sidebarVisible = computed(() => !isMobile.value || sidebarOpen.value)

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

async function logout() {
  await authStore.logout()
  router.push('/')
}

onMounted(() => {
  const updateIsMobile = () => {
    isMobile.value = window.matchMedia('(max-width: 767px)').matches
  }
  updateIsMobile()
  const media = window.matchMedia('(max-width: 767px)')
  media.addEventListener('change', updateIsMobile)
})
</script>

<template>
  <div class="relative min-h-screen">
    <!-- Mobile Menu Button -->
    <button @click="toggleSidebar" class="md:hidden fixed top-4 left-4 z-30 p-4">
      ☰
    </button>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 flex flex-col z-20 transition-transform duration-300"
      :class="{ 'translate-x-0': sidebarVisible, '-translate-x-full': !sidebarVisible }"
    >
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
    <main
      class="flex-1 p-6 overflow-auto transition-margin duration-300"
      :class="{ 'ml-64': !isMobile }"
    >
      <slot />
    </main>
  </div>
</template>
