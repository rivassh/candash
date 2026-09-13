<script setup lang="ts">
definePageMeta({ layout: 'blank' })
const router = useRouter()

function goLogin() {
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-bl from-primary-700 to-primary-900">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md text-center">
      <h1 class="text-2xl font-bold text-gray-800">TalentMatch</h1>
      <p class="text-gray-500 text-sm mt-1">سامانه هوشمند مدیریت استعداد</p>
      <button @click="goLogin" class="mt-6 btn-primary w-full py-2.5">
        ورود به سیستم
      </button>
    </div>
  </div>
</template>
