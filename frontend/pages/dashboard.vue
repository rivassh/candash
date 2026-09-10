<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const { data: summary, pending, refresh } = await useAsyncData('dashboard', () =>
  api.get<any>('/dashboard/summary')
)

const scoreColors: Record<string, string> = {
  pending: 'bg-gray-400',
  reviewed: 'bg-blue-400',
  shortlisted: 'bg-green-400',
  rejected: 'bg-red-400',
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800">داشبورد</h1>
      <button @click="refresh()" class="btn-secondary text-xs">🔄 بروزرسانی</button>
    </div>

    <div v-if="pending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <template v-else-if="summary">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="stat-card">
          <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-lg">👤</div>
          <div>
            <p class="text-2xl font-bold text-gray-800">{{ summary.candidates.total }}</p>
            <p class="text-sm text-gray-500">کل کاندیداها</p>
          </div>
          <div class="mr-auto space-y-1 text-left">
            <span class="badge-success">{{ summary.candidates.new }} جدید</span>
            <span class="badge-info">{{ summary.candidates.in_review }} در حال بررسی</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-lg">💼</div>
          <div>
            <p class="text-2xl font-bold text-gray-800">{{ summary.positions.total }}</p>
            <p class="text-sm text-gray-500">موقعیت‌های شغلی</p>
          </div>
          <div class="mr-auto">
            <span class="badge-success">{{ summary.positions.open }} باز</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center text-lg">🔗</div>
          <div>
            <p class="text-2xl font-bold text-gray-800">{{ summary.matches.total }}</p>
            <p class="text-sm text-gray-500">نتایج تطبیق</p>
          </div>
          <div class="mr-auto space-y-1 text-left">
            <span class="badge-success">{{ summary.matches.shortlisted }} تایید شده</span>
            <span class="badge-info">میانگین: {{ summary.matches.avg_score }}٪</span>
          </div>
        </div>
      </div>

      <!-- Top Matches -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-800 mb-4">برترین تطابق‌ها</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="table-header">
                <th class="px-4 py-3 text-right">کاندیدا</th>
                <th class="px-4 py-3 text-right">موقعیت</th>
                <th class="px-4 py-3 text-right">امتیاز</th>
                <th class="px-4 py-3 text-right">وضعیت</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in summary.top_matches" :key="m.id" class="table-row">
                <td class="px-4 py-3">{{ m.candidate_name }}</td>
                <td class="px-4 py-3">{{ m.position_title }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="score-bar w-20">
                      <div class="score-fill bg-green-500" :style="{ width: m.total_score + '%' }"></div>
                    </div>
                    <span class="font-medium text-green-600">{{ m.total_score }}٪</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span :class="scoreColors[m.status]?.replace('bg-', 'badge-') || 'badge-gray'">
                    {{ { pending: 'در انتظار', reviewed: 'بررسی شده', shortlisted: 'تایید شده', rejected: 'رد شده' }[m.status] || m.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="!summary.top_matches?.length">
                <td colspan="4" class="px-4 py-8 text-center text-gray-400">هنوز تطبیقی انجام نشده است.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>