<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const route = useRoute()

const { data: resume, pending } = await useAsyncData(`resume-${route.params.applicationId}`, () =>
  api.get<any>(`/job-positions/${route.params.id}/applications/${route.params.applicationId}/resume`)
)

const { data: position, pending: positionPending } = await useAsyncData(`position-${route.params.id}`, () =>
  api.get<any>(`/JobPositions/${route.params.id}`)
)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <NuxtLink to="/positions" class="btn-secondary text-sm">← بازگشت</NuxtLink>
        <h1 class="text-xl font-bold text-gray-800">
          رزومه - {{ position?.data?.title || 'موقعیت شغلی' }}
        </h1>
      </div>
      <button @click="refresh()" class="btn-secondary text-sm" :disabled="pending">🔄 تازه‌سازی</button>
    </div>

    <div v-if="pending || positionPending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="card overflow-hidden p-6">
      <div v-if="resume?.resumeText" class="prose prose-sm max-h-[600px] overflow-y-auto whitespace-pre-wrap">
        {{ resume.resumeText }}
      </div>
      <p v-else class="text-gray-400 text-sm">رزومه‌ای ثبت نشده است.</p>
    </div>
  </div>
</template>