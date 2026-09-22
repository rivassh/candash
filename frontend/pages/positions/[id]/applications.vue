<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const route = useRoute()

const { data: applications, pending, refresh } = await useAsyncData(`applications-${route.params.id}`, () =>
  api.get<any>(`/job-positions/${route.params.id}/applications`)
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
          درخواست‌ها برای {{ position?.data?.title || 'موقعیت شغلی' }}
        </h1>
        <span v-if="applications?.count" class="badge-info text-xs">
          {{ applications.count }} درخواست
        </span>
      </div>
      <button @click="refresh()" class="btn-secondary text-sm" :disabled="pending">🔄 تازه‌سازی</button>
    </div>

    <div v-if="pending || positionPending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="card overflow-hidden p-0">
      <table class="w-full text-sm">
        <thead>
          <tr class="table-header">
            <th class="px-4 py-3 text-right">نام کاربر</th>
            <th class="px-4 py-3 text-right">ایمیل</th>
            <th class="px-4 py-3 text-right">تلفن</th>
            <th class="px-4 py-3 text-right">وضعیت</th>
            <th class="px-4 py-3 text-right">تاریخ ثبت</th>
            <th class="px-4 py-3 text-right">عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in applications?.data" :key="app.applicationId" class="table-row">
            <td class="px-4 py-3 font-medium">{{ app.candidateName || 'نامشخص' }}</td>
            <td class="px-4 py-3">{{ app.email || '—' }}</td>
            <td class="px-4 py-3">{{ app.phone || '—' }}</td>
            <td class="px-4 py-3">
              <span :class="statusColors[app.status] || 'badge-gray'">
                {{ statusLabels[app.status] || app.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-500 text-xs">{{ app.submittedAt || '—' }}</td>
            <td class="px-4 py-3">
              <NuxtLink :to="`/positions/${route.params.id}/applications/${app.applicationId}`" class="btn-secondary text-xs">
                جزئیات
              </NuxtLink>
              <NuxtLink :to="`/positions/${route.params.id}/applications/${app.applicationId}/resume`" class="btn-primary text-xs">
                رزومه
              </NuxtLink>
            </td>
          </tr>
          <tr v-if="!applications?.data?.length">
            <td colspan="6" class="px-4 py-8 text-center text-gray-400">درخواستی یافت نشد.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>