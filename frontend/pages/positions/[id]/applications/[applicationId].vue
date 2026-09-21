<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const route = useRoute()

const { data: application, pending, refresh } = await useAsyncData(`application-${route.params.applicationId}`, () =>
  api.get<any>(`/job-positions/${route.params.id}/applications/${route.params.applicationId}`)
)

const { data: position, pending: positionPending } = await useAsyncData(`position-${route.params.id}`, () =>
  api.get<any>(`/JobPositions/${route.params.id}`)
)

const statusColors: Record<string, string> = {
  pending: 'badge-gray', review: 'badge-warning', shortlisted: 'badge-success', rejected: 'badge-danger', hired: 'badge-success',
}
const statusLabels: Record<string, string> = {
  pending: 'در انتظار', review: 'بررسی', shortlisted: 'کوتاه‌شده', rejected: 'رد شده', hired: 'استخدام شده',
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <NuxtLink to="/positions" class="btn-secondary text-sm">← بازگشت</NuxtLink>
        <h1 class="text-xl font-bold text-gray-800">
          درخواست {{ application?.applicationId }} - {{ position?.data?.title || 'موقعیت شغلی' }}
        </h1>
        <span v-if="application?.status" :class="statusColors[application.status] || 'badge-gray'">
          {{ statusLabels[application.status] || application.status }}
        </span>
      </div>
      <button @click="refresh()" class="btn-secondary text-sm" :disabled="pending">🔄 تازه‌سازی</button>
    </div>

    <div v-if="pending || positionPending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="card overflow-hidden p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Application Details -->
        <div>
          <h2 class="text-lg font-semibold text-gray-800 mb-4">جزئیات درخواست</h2>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <p class="text-xs text-gray-500">نام کاربر</p>
              <p class="text-sm font-medium">{{ application?.candidateName || 'نامشخص' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">ایمیل</p>
              <p class="text-sm font-medium">{{ application?.email || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">شماره تماس</p>
              <p class="text-sm font-medium">{{ application?.phone || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">وضعیت</p>
              <p class="text-sm font-medium">{{ statusLabels[application?.status] || application?.status }}</p>
            </div>
          </div>
          <div>
            <p class="text-xs text-gray-500">تاریخ ثبت</p>
            <p class="text-sm">{{ application?.submittedAt || '—' }}</p>
          </div>
        </div>

        <!-- Resume Viewer -->
        <div>
          <h2 class="text-lg font-semibold text-gray-800 mb-4">رزومه / CV</h2>
          <div v-if="application?.resumeText" class="prose prose-sm max-h-[600px] overflow-y-auto whitespace-pre-wrap">
            {{ application.resumeText }}
          </div>
          <p v-else class="text-gray-400 text-sm">رزومه‌ای ثبت نشده است.</p>
        </div>
      </div>
    </div>
  </div>
</template>