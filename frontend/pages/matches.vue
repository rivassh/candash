<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()

const candidates = ref<any[]>([])
const positions = ref<any[]>([])
const selectedCandidates = ref<number[]>([])
const selectedPosition = ref<number | null>(null)
const results = ref<any[]>([])
const loading = ref(false)
const error = ref('')
const hasRun = ref(false)

const candidatesRes = await useAsyncData('match-candidates', () =>
  api.get<any>('/candidates', { paginate: 'false' })
)
const positionsRes = await useAsyncData('match-positions', () =>
  api.get<any>('/job-positions')
)

watchEffect(() => {
  if (candidatesRes.data.value) {
    candidates.value = candidatesRes.data.value.data || []
  }
  if (positionsRes.data.value) {
    positions.value = positionsRes.data.value.data || []
  }
})

async function runMatch() {
  error.value = ''
  if (!selectedPosition.value) {
    error.value = 'لطفاً یک موقعیت شغلی انتخاب کنید.'
    return
  }
  if (selectedCandidates.value.length === 0) {
    error.value = 'لطفاً حداقل یک کاندیدا انتخاب کنید.'
    return
  }

  loading.value = true
  try {
    const res = await api.post<any>('/match/run', {
      candidate_ids: selectedCandidates.value,
      job_position_id: selectedPosition.value,
    })
    results.value = res.data || []
    hasRun.value = true
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

async function updateStatus(result: any, status: string) {
  try {
    await api.patch<any>(`/match/results/${result.id}/status`, { status })
    result.status = status
  } catch (e: any) {
    alert(e.message)
  }
}

function scoreColor(score: number) {
  if (score >= 70) return 'bg-green-500'
  if (score >= 50) return 'bg-yellow-500'
  return 'bg-red-400'
}

function scoreLabel(score: number) {
  if (score >= 70) return 'عالی'
  if (score >= 50) return 'متوسط'
  return 'ضعیف'
}

const weights = { required_skills: 40, experience: 20, seniority: 15, education: 10, preferred_skills: 10, stability: 5 }
</script>

<template>
  <div>
    <h1 class="text-xl font-bold text-gray-800 mb-6">موتور تطبیق</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Selection Panel -->
      <div class="lg:col-span-1 space-y-4">
        <div class="card">
          <h2 class="font-semibold text-gray-800 mb-3">انتخاب موقعیت شغلی</h2>
          <select v-model="selectedPosition" class="input">
            <option :value="null">انتخاب کنید...</option>
            <option v-for="p in positions" :key="p.id" :value="p.id">{{ p.title }}</option>
          </select>
        </div>

        <div class="card">
          <h2 class="font-semibold text-gray-800 mb-3">انتخاب کاندیداها</h2>
          <div class="space-y-2 max-h-80 overflow-y-auto">
            <label v-for="c in candidates" :key="c.id"
              class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer text-sm">
              <input type="checkbox" :value="c.id" v-model="selectedCandidates"
                class="w-4 h-4 text-primary-600 rounded border-gray-300" />
              <span>{{ c.name }}</span>
            </label>
          </div>
          <p class="text-xs text-gray-400 mt-2">{{ selectedCandidates.length }} کاندیدا انتخاب شده</p>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
          {{ error }}
        </div>

        <button @click="runMatch" class="btn-primary w-full" :disabled="loading">
          {{ loading ? 'در حال تطبیق...' : '▶ اجرای تطبیق' }}
        </button>
      </div>

      <!-- Results Panel -->
      <div class="lg:col-span-2 space-y-4">
        <div v-if="!hasRun" class="card text-center py-16 text-gray-400">
          پس از انتخاب موقعیت و کاندیداها، روی «اجرای تطبیق» کلیک کنید.
        </div>

        <div v-else-if="results.length === 0" class="card text-center py-16 text-gray-400">
          نتیجه‌ای یافت نشد.
        </div>

        <div v-else class="space-y-4">
          <div v-for="result in results" :key="result.id" class="card">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h3 class="font-semibold text-gray-800">{{ result.candidate?.name }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ result.job_position?.title }}</p>
              </div>
              <div class="flex items-center gap-3">
                <div class="text-left">
                  <div class="flex items-center gap-2 mb-1">
                    <div class="score-bar w-24">
                      <div class="score-fill" :class="scoreColor(result.total_score)"
                        :style="{ width: result.total_score + '%' }"></div>
                    </div>
                    <span class="font-bold text-lg">{{ result.total_score }}٪</span>
                  </div>
                  <span :class="['text-xs font-medium', scoreColor(result.total_score).replace('bg-', 'text-')]">
                    {{ scoreLabel(result.total_score) }}
                  </span>
                </div>
                <select :value="result.status"
                  @change="updateStatus(result, ($event.target as HTMLSelectElement).value)"
                  class="input w-auto text-xs">
                  <option value="pending">در انتظار</option>
                  <option value="reviewed">بررسی شده</option>
                  <option value="shortlisted">تایید اولیه</option>
                  <option value="rejected">رد شده</option>
                </select>
              </div>
            </div>

            <!-- Breakdown Chart -->
            <div class="mb-4">
              <p class="text-xs font-medium text-gray-600 mb-2">ریز امتیاز</p>
              <div class="space-y-1.5">
                <div v-for="(weight, key) in weights" :key="key" class="flex items-center gap-2 text-xs">
                  <span class="w-32 text-left">{{ {
                    required_skills: 'مهارت‌های الزامی',
                    experience: 'سوابق کاری',
                    seniority: 'سطح ارشدیت',
                    education: 'تحصیلات',
                    preferred_skills: 'مهارت‌های ترجیحی',
                    stability: 'پایداری شغلی',
                  }[key] }}</span>
                  <div class="flex-1 score-bar">
                    <div class="score-fill bg-primary-500"
                      :style="{ width: ((result.breakdown?.[key]?.score || 0) * weight / 100) + '%' }"></div>
                  </div>
                  <span class="w-12 text-left font-medium">{{ result.breakdown?.[key]?.score || 0 }}٪</span>
                </div>
              </div>
            </div>

            <!-- Strengths & Gaps -->
            <div class="grid grid-cols-2 gap-4">
              <div v-if="result.strengths?.length">
                <p class="text-xs font-medium text-green-700 mb-1">✓ نقاط قوت</p>
                <ul class="space-y-1">
                  <li v-for="(s, i) in result.strengths" :key="i"
                    class="text-xs text-green-700 bg-green-50 rounded px-2 py-1">
                    {{ s }}
                  </li>
                </ul>
              </div>
              <div v-if="result.gaps?.length">
                <p class="text-xs font-medium text-red-700 mb-1">✕ کمبودها</p>
                <ul class="space-y-1">
                  <li v-for="(g, i) in result.gaps" :key="i"
                    class="text-xs text-red-700 bg-red-50 rounded px-2 py-1">
                    {{ g }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>