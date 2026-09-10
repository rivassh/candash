<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const route = useRoute()

const { data: candidate, pending, refresh } = await useAsyncData(`candidate-${route.params.id}`, () =>
  api.get<any>(`/candidates/${route.params.id}`)
)

const enrichLoading = ref(false)
const enrichResult = ref<any>(null)

async function enrichLinkedin() {
  if (!candidate.value?.linkedin_url) {
    alert('لینکدین URL موجود نیست')
    return
  }
  enrichLoading.value = true
  try {
    enrichResult.value = await api.post<any>(`/candidates/${route.params.id}/enrich-linkedin`, {
      linkedin_url: candidate.value.linkedin_url,
    })
    await refresh()
  } catch (e: any) {
    alert(e.message)
  } finally {
    enrichLoading.value = false
  }
}

const statusColors: Record<string, string> = {
  new: 'badge-info', in_review: 'badge-warning', shortlisted: 'badge-success', rejected: 'badge-danger', hired: 'badge-success',
}

function getConfidenceColor(conf: number) {
  if (conf >= 0.8) return 'text-green-600'
  if (conf >= 0.6) return 'text-yellow-600'
  return 'text-red-600'
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <NuxtLink to="/candidates" class="btn-secondary text-sm">← بازگشت</NuxtLink>
        <h1 class="text-xl font-bold text-gray-800">{{ candidate?.name }}</h1>
        <span v-if="candidate" :class="statusColors[candidate.status] || 'badge-gray'">
          {{ { new: 'جدید', in_review: 'در حال بررسی', shortlisted: 'تایید اولیه', rejected: 'رد شده', hired: 'استخدام شده' }[candidate.status] || candidate.status }}
        </span>
      </div>
      <button
        v-if="candidate?.linkedin_url"
        @click="enrichLinkedin"
        class="btn-primary"
        :disabled="enrichLoading"
      >
        {{ enrichLoading ? 'در حال غنی‌سازی...' : '🔗 شبیه‌سازی غنی‌سازی لینکدین' }}
      </button>
    </div>

    <div v-if="pending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else-if="candidate" class="space-y-6">
      <!-- Info Card -->
      <div class="card">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <p class="text-xs text-gray-500">پست الکترونیکی</p>
            <p class="text-sm font-medium">{{ candidate.email || '—' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">شماره تماس</p>
            <p class="text-sm font-medium">{{ candidate.phone || '—' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">لینکدین</p>
            <a v-if="candidate.linkedin_url" :href="candidate.linkedin_url" target="_blank"
              class="text-sm text-primary-600 hover:underline">
              مشاهده پروفایل
            </a>
            <p v-else class="text-sm text-gray-400">—</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">خلاصه</p>
            <p class="text-sm">{{ candidate.summary || '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Skills -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-800 mb-3">مهارت‌ها</h2>
        <div v-if="candidate.skills?.length" class="flex flex-wrap gap-2">
          <div v-for="s in candidate.skills" :key="s.id"
            class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <span class="font-medium">{{ s.name }}</span>
            <span class="text-gray-400 mr-2">{{ s.years_experience }} سال</span>
            <span :class="['text-xs mr-2 font-medium', getConfidenceColor(s.confidence)]">
              ({{ Math.round(s.confidence * 100) }}٪)
            </span>
          </div>
        </div>
        <p v-else class="text-gray-400 text-sm">مهارتی ثبت نشده است.</p>
      </div>

      <!-- Experiences -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-800 mb-3">سوابق کاری</h2>
        <div v-if="candidate.experiences?.length" class="space-y-4">
          <div v-for="exp in candidate.experiences" :key="exp.id"
            class="flex gap-4 border-b border-gray-100 pb-4 last:border-0 last:pb-0">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <p class="font-medium text-gray-800">{{ exp.job_title }}</p>
                <span v-if="exp.is_current" class="badge-success text-xs">فعلی</span>
              </div>
              <p class="text-sm text-gray-600">{{ exp.company }}</p>
              <p class="text-xs text-gray-400 mt-1">
                {{ exp.start_date?.slice(0, 10) || '?' }}
                تا
                {{ exp.is_current ? 'تاکنون' : (exp.end_date?.slice(0, 10) || '?') }}
                ({{ exp.duration_years }} سال)
              </p>
            </div>
            <div class="text-left">
              <span :class="['text-xs font-medium', getConfidenceColor(exp.confidence)]">
                منبع: {{ exp.source }} ({{ Math.round(exp.confidence * 100) }}٪)
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-400 text-sm">سابقه‌ای ثبت نشده است.</p>
      </div>

      <!-- Education -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-800 mb-3">تحصیلات</h2>
        <div v-if="candidate.educations?.length" class="space-y-3">
          <div v-for="edu in candidate.educations" :key="edu.id" class="flex gap-4">
            <div class="flex-1">
              <p class="font-medium text-gray-800">{{ edu.degree }} — {{ edu.field_of_study }}</p>
              <p class="text-sm text-gray-600">{{ edu.institution }}</p>
              <p v-if="edu.graduation_year" class="text-xs text-gray-400">فارغ‌التحصیلی: {{ edu.graduation_year }}</p>
            </div>
            <span :class="['text-xs font-medium self-center', getConfidenceColor(edu.confidence)]">
              ({{ Math.round(edu.confidence * 100) }}٪)
            </span>
          </div>
        </div>
        <p v-else class="text-gray-400 text-sm">تحصیلی ثبت نشده است.</p>
      </div>
    </div>
  </div>
</template>