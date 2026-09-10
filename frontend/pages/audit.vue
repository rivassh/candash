<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const { data, pending, refresh } = await useAsyncData('audit', () =>
  api.get<any>('/audit-logs')
)

function formatDate(d: string) {
  return new Date(d).toLocaleString('fa-IR')
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800">گزارش تغییرات</h1>
      <button @click="refresh()" class="btn-secondary text-xs">🔄 بروزرسانی</button>
    </div>

    <div v-if="pending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="card overflow-hidden p-0">
      <table class="w-full text-sm">
        <thead>
          <tr class="table-header">
            <th class="px-4 py-3 text-right">زمان</th>
            <th class="px-4 py-3 text-right">کاربر</th>
            <th class="px-4 py-3 text-right">مدل</th>
            <th class="px-4 py-3 text-right">عملیات</th>
            <th class="px-4 py-3 text-right">IP</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in data?.data" :key="log.id" class="table-row">
            <td class="px-4 py-3 text-xs text-gray-500">{{ formatDate(log.created_at) }}</td>
            <td class="px-4 py-3">{{ log.user?.name || 'سیستم' }}</td>
            <td class="px-4 py-3">
              <span class="badge-info text-xs">{{ log.model_type }}</span>
              <span class="text-gray-400 mr-1">#{{ log.model_id }}</span>
            </td>
            <td class="px-4 py-3">
              <span :class="{
                'badge-info': log.action.includes('create') || log.action.includes('update'),
                'badge-success': log.action.includes('enrich') || log.action.includes('status'),
                'badge-warning': log.action.includes('delete'),
              }">
                {{ log.action }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-400">{{ log.ip_address || '—' }}</td>
          </tr>
          <tr v-if="!data?.data?.length">
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">گزارشی یافت نشد.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>