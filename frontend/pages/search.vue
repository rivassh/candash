<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()

const query = ref('')
const searchMode = ref('candidates')
const statusFilter = ref('')
const skillFilter = ref('')
const minExperience = ref('')
const jobPositionId = ref<number | null>(null)
const results = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const loading = ref(false)
const error = ref('')
const hasSearched = ref(false)
const showMatch = ref(false)

const positions = ref<any[]>([])
const jobPositionsLoaded = ref(false)

async function loadJobPositions() {
  try {
    const res = await api.get<any>('/JobPositions?paginate=false')
    positions.value = res.data || []
    jobPositionsLoaded.value = true
  } catch (e) {
    // ignore
  }
}

async function search(resetPage = true) {
  loading.value = true
  error.value = ''
  if (resetPage) {
    page.value = 1
  }
  hasSearched.value = true
  showMatch.value = false

  if (searchMode.value === 'jobs') {
    await searchJobs();
    return;
  }

  const params: Record<string, string> = {
    q: query.value,
    page: String(page.value),
  }

  if (searchMode.value === 'candidates') {
    if (statusFilter.value) params.status = statusFilter.value
    if (skillFilter.value) params.skill_ids = skillFilter.value
    if (minExperience.value) params.min_experience = minExperience.value
  } else if (searchMode.value === 'matches') {
    if (jobPositionId.value) params.job_position_id = String(jobPositionId.value)
  }

  try {
    const endpoint = searchMode.value === 'matches' ? '/search/match' : '/search/candidates'
    const res = await api.get<any>(endpoint, params)
    results.value = res.data || []
    total.value = res.meta?.total || 0
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

async function searchJobs(resetPage = true) {
  loading.value = true
  error.value = ''
  if (resetPage) {
    page.value = 1
  }
  hasSearched.value = true
  showMatch.value = false

  const params: Record<string, string> = {
    q: query.value,
    page: String(page.value),
  }

  try {
    const res = await api.get<any>('/search/jobs', params)
    results.value = res.data || []
    total.value = res.meta?.total || 0
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

function nextPage() {
  page.value++
  if (searchMode.value === 'jobs') {
    searchJobs(false)
  } else {
    search(false)
  }
}

function prevPage() {
  if (page.value > 1) {
    page.value--
    if (searchMode.value === 'jobs') {
      searchJobs(false)
    } else {
      search(false)
    }
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

function matchScoreColor(score: number | null) {
  if (score === null) return 'bg-gray-400'
  if (score >= 70) return 'bg-green-500'
  if (score >= 50) return 'bg-yellow-500'
  return 'bg-red-400'
}

const statusLabels: Record<string, string> = {
  new: 'جدید',
  in_review: 'در حال بررسی',
  shortlisted: 'تایید شده',
  rejected: 'رد شده',
}

function toggleMatch() {
  showMatch.value = !showMatch.value
}
</script>

<template>
  <div>
    <h1 class="text-xl font-bold text-gray-800 mb-6">جستجوی هوشمند</h1>

    <div class="card mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
          <label class="label">حالت جستجو</label>
          <select v-model="searchMode" class="input" @change="search">
            <option value="candidates">کاندیداها</option>
            <option value="matches">تطبیق کاندیدها با موقعیت</option>
            <option value="jobs">موقعیت‌های شغلی</option>
          </select>
        </div>
        <div v-if="searchMode === 'matches'">
          <label class="label">موقعیت شغلی</label>
          <select v-model="jobPositionId" class="input">
            <option :value="null">انتخاب کنید...</option>
            <option v-for="p in positions" :key="p.id" :value="p.id">{{ p.title }}</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="searchMode === 'jobs' ? searchJobs() : search()" class="btn-primary w-full" :disabled="loading">
            {{ loading ? 'در حال جستجو...' : '🔍 جستجو' }}
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-2">
          <label class="label">جستجو</label>
          <input v-model="query" @keydown.enter="searchMode === 'jobs' ? searchJobs() : search()" class="input" placeholder="نام، مهارت، ایمیل، عنوان..." />
        </div>
        <div v-if="searchMode === 'candidates'">
          <label class="label">وضعیت</label>
          <select v-model="statusFilter" class="input" @change="search">
            <option value="">همه وضعیت‌ها</option>
            <option value="new">جدید</option>
            <option value="in_review">در حال بررسی</option>
            <option value="shortlisted">تایید شده</option>
            <option value="rejected">رد شده</option>
          </select>
        </div>
        <div v-if="searchMode === 'candidates'">
          <label class="label">حداقل سابقه (سال)</label>
          <input v-model="minExperience" type="number" min="0" class="input" placeholder="مثال: 3" @change="search" />
        </div>
      </div>

      <div v-if="searchMode === 'candidates'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="label">فیلتر مهارت (ID)</label>
          <input v-model="skillFilter" class="input" placeholder="مثال: 1,2,3" @change="search" />
        </div>
      </div>
    </div>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-6">
      {{ error }}
    </div>

    <div v-if="!hasSearched" class="card text-center py-16 text-gray-400">
      برای شروع، یک عبارت جستجو کنید یا فیلتر دلخواه را اعمال کنید.
    </div>

    <div v-else-if="loading" class="card text-center py-12 text-gray-400">
      در حال جستجو...
    </div>

    <div v-else-if="results.length === 0" class="card text-center py-16 text-gray-400">
      نتیجه‌ای یافت نشد.
    </div>

    <div v-else>
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">
          {{ total }} نتیجه یافت شد
        </p>
        <div class="flex gap-2">
          <button @click="prevPage" :disabled="page <= 1" class="btn-secondary text-xs">قبلی</button>
          <button @click="nextPage" :disabled="page * 20 >= total" class="btn-secondary text-xs">بعدی</button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="r in results" :key="r.id" class="card">
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-semibold text-gray-800">{{ r.name || r.title }}</h3>
              <p class="text-xs text-gray-400 mt-1">{{ r.email || r.department }}</p>
              <p class="text-xs text-gray-400">{{ r.phone || r.level }}</p>
            </div>
            <div class="text-left">
              <div class="flex items-center gap-2 mb-1">
                <div class="score-bar w-20">
                  <div class="score-fill" :class="scoreColor(r.search_score || r.score)" :style="{ width: Math.min(r.search_score || r.score, 100) + '%' }"></div>
                </div>
                <span class="font-bold text-lg">{{ r.search_score || r.score }}</span>
              </div>
              <span class="text-xs font-medium text-gray-500">امتیاز جستجو</span>
              <div v-if="r.match_score !== null && r.match_score !== undefined" class="mt-1">
                <div class="flex items-center gap-2">
                  <div class="score-bar w-20">
                    <div class="score-fill" :class="matchScoreColor(r.match_score)" :style="{ width: Math.min(r.match_score, 100) + '%' }"></div>
                  </div>
                  <span class="font-bold text-sm" :class="matchScoreColor(r.match_score).replace('bg-', 'text-')">{{ r.match_score }}٪</span>
                </div>
                <span class="text-xs text-gray-500">امتیاز تطبیق</span>
              </div>
            </div>
          </div>

          <div v-if="r.skills?.length" class="flex flex-wrap gap-1 mb-3">
            <span v-for="s in r.skills" :key="s" class="badge-info text-xs">{{ s }}</span>
          </div>

          <div v-if="r.breakdown" class="mt-2">
            <p class="text-xs font-medium text-gray-600 mb-1">ریز تطبیق:</p>
            <div class="space-y-1">
              <div v-for="(weight, key) in { required_skills: 40, experience: 20, seniority: 15, education: 10, preferred_skills: 10, stability: 5 }" :key="key" class="flex items-center gap-2 text-xs">
                <span class="w-28 text-left">{{ {
                  required_skills: 'مهارت الزامی',
                  experience: 'سوابق',
                  seniority: 'ارشدیت',
                  education: 'تحصیلات',
                  preferred_skills: 'مهارت ترجیحی',
                  stability: 'پایداری',
                }[key] }}</span>
                <div class="flex-1 score-bar">
                  <div class="score-fill bg-primary-500" :style="{ width: ((r.breakdown?.[key]?.score || 0) * weight / 100) + '%' }"></div>
                </div>
                <span class="w-12 text-left">{{ r.breakdown?.[key]?.score || 0 }}٪</span>
              </div>
            </div>
          </div>

          <div v-if="r.strengths?.length" class="mt-2">
            <p class="text-xs font-medium text-green-700 mb-1">✓ نقاط قوت</p>
            <ul class="space-y-1">
              <li v-for="(s, i) in r.strengths" :key="i" class="text-xs text-green-700 bg-green-50 rounded px-2 py-1">{{ s }}</li>
            </ul>
          </div>
          <div v-if="r.gaps?.length" class="mt-2">
            <p class="text-xs font-medium text-red-700 mb-1">✕ کمبودها</p>
            <ul class="space-y-1">
              <li v-for="(g, i) in r.gaps" :key="i" class="text-xs text-red-700 bg-red-50 rounded px-2 py-1">{{ g }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>