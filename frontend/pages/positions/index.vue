<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const router = useRouter()

const { data, pending, refresh } = await useAsyncData('positions', () =>
  api.get<any>('/JobPositions')
)

const showModal = ref(false)
const showImportModal = ref(false)
const importError = ref('')
const importCurl = ref('')
const form = ref({
  title: '', department: '', level: 'mid', employment_type: 'full_time',
  min_experience_years: 0, education_requirements: '', description: '',
  required_skills: [{ name: '', weight: 5, min_years: 0 }],
  preferred_skills: [] as string[],
})
const saving = ref(false)
const error = ref('')

const levels = ['junior', 'mid', 'senior']
const levelLabels: Record<string, string> = { junior: 'جونیور', mid: 'میدلول', senior: 'سنیور' }
const empTypes = ['full_time', 'part_time', 'contract', 'internship']
const empLabels: Record<string, string> = {
  full_time: 'تمام‌وقت', part_time: 'پاره‌وقت', contract: 'قراردادی', internship: 'کارآموزی'
}
const statusColors: Record<string, string> = {
  draft: 'badge-gray', open: 'badge-success', closed: 'badge-danger', archived: 'badge-warning'
}

function addSkill() {
  form.value.required_skills.push({ name: '', weight: 5, min_years: 0 })
}

function removeSkill(i: number) {
  form.value.required_skills.splice(i, 1)
}

function addPreferred(skill: string) {
  if (skill && !form.value.preferred_skills.includes(skill)) {
    form.value.preferred_skills.push(skill)
  }
}

async function submit() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/JobPositions', {
      ...form.value,
      required_skills: form.value.required_skills.filter(s => s.name.trim()),
    })
    showModal.value = false
    form.value = { title: '', department: '', level: 'mid', employment_type: 'full_time',
      min_experience_years: 0, education_requirements: '', description: '',
      required_skills: [{ name: '', weight: 5, min_years: 0 }], preferred_skills: [] }
    await refresh()
  } catch (e: any) {
    error.value = e.message
  } finally {
    saving.value = false
  }
}

async function importFromSource() {
  if (!confirm('ایمپورت موقعیت‌ها از منبع خارجی؟')) return
  try {
    const result = await api.post<any>('/job-positions/import-from-source')
    alert(`ایجاد: ${result.created} | به‌روزرسانی: ${result.updated}`)
    await refresh()
  } catch (e: any) {
    importError.value = e.message
    try {
      const res = await api.get<{ curl: string }>('/job-positions/import-curl')
      importCurl.value = res.curl
    } catch {
      importCurl.value = `curl 'https://employerapi.jobvision.ir/api/v1.0/JobPost/GetListOfJobPosts' \\
  --compressed \\
  -X POST \\
  -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0' \\
  -H 'Accept: application/json, text/plain, */*' \\
  -H 'Accept-Language: en-US,en;q=0.5' \\
  -H 'Accept-Encoding: gzip, deflate, br, zstd' \\
  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\
  -H 'Content-Type: application/json' \\
  -H 'Origin: https://employer.jobvision.ir' \\
  -H 'Connection: keep-alive' \\
  -H 'Referer: https://employer.jobvision.ir/' \\
  -H 'Sec-Fetch-Dest: empty' \\
  -H 'Sec-Fetch-Mode: cors' \\
  -H 'Sec-Fetch-Site: same-site' \\
  -H 'Priority: u=0' \\
  -H 'TE: trailers' \\
  --data-raw '{"statusId":3,"keyword":"","pageNumber":1,"pageSize":10}'`
    }
    showImportModal.value = true
  }
}

function viewApplications(position: any) {
  router.push(`/positions/${position.external_id}/applications`)
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800">موقعیت‌های شغلی</h1>
      <div class="flex gap-2">
        <button @click="importFromSource()" class="btn-secondary">📥 ایمپورت از منبع</button>
        <button @click="showModal = true" class="btn-primary">+ موقعیت جدید</button>
      </div>
    </div>

    <div v-if="pending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="card overflow-hidden p-0">
      <table class="w-full text-sm">
        <thead>
          <tr class="table-header">
            <th class="px-4 py-3 text-right">عنوان</th>
            <th class="px-4 py-3 text-right">دپارتمان</th>
            <th class="px-4 py-3 text-right">سطح</th>
            <th class="px-4 py-3 text-right">نوع استخدام</th>
            <th class="px-4 py-3 text-right">حداقل سابقه</th>
            <th class="px-4 py-3 text-right">مهارت‌های الزامی</th>
            <th class="px-4 py-3 text-right">وضعیت</th>
            <th class="px-4 py-3 text-right">عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pos in data?.data" :key="pos.id" class="table-row" @click="viewApplications(pos)">
            <td class="px-4 py-3 font-medium">{{ pos.title }}</td>
            <td class="px-4 py-3">{{ pos.department }}</td>
            <td class="px-4 py-3">{{ levelLabels[pos.level] || pos.level }}</td>
            <td class="px-4 py-3">{{ empLabels[pos.employment_type] || pos.employment_type }}</td>
            <td class="px-4 py-3">{{ pos.min_experience_years }} سال</td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap gap-1">
                <span v-for="s in (pos.required_skills || []).slice(0, 3)" :key="s.name"
                  class="badge-info text-xs">
                  {{ s.name }} ({{ s.weight }})
                </span>
                <span v-if="(pos.required_skills || []).length > 3" class="badge-gray text-xs">
                  +{{ pos.required_skills.length - 3 }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3">
              <span :class="statusColors[pos.status] || 'badge-gray'">
                {{ { draft: 'پیش‌نویس', open: 'باز', closed: 'بسته', archived: 'آرشیو' }[pos.status] || pos.status }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button class="btn-secondary text-xs" @click.stop="viewApplications(pos)">📋 درخواست‌ها</button>
            </td>
          </tr>
          <tr v-if="!data?.data?.length">
            <td colspan="8" class="px-4 py-8 text-center text-gray-400">موقعیتی یافت نشد.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Import Error Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 mx-4">
        <h2 class="text-lg font-bold mb-4">خطای احراز هویت در ایمپورت از منبع</h2>
        
        <div v-if="importError" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
          {{ importError }}
        </div>

        <div class="mb-4">
          <p class="text-sm font-medium mb-2">برای رفع خطا، توکن احراز هویت JobVision را به‌روزرسانی کنید:</p>
          <div class="font-mono text-xs bg-gray-50 p-3 rounded border" v-text="importCurl"></div>
          <button @click="navigator.clipboard.writeText(importCurl).then(() => alert('لیست curl کپی شد'))"
                  class="btn-secondary mt-2">
            📋 کپی کردن دستور curl
          </button>
        </div>

        <div class="mt-4">
          <p class="text-sm font-medium mb-2">یا می‌توانید از پنل مدیریت برای به‌روزرسانی اطلاعات احراز هویت استفاده کنید:</p>
          <button @click="showImportModal = false; router.push('/admin/jobvision-credentials')"
                  class="btn-primary">
            ⚙️ رفتن به پنل تنظیمات JobVision
          </button>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t">
          <button type="button" @click="showImportModal = false" class="btn-secondary">بستن</button>
          <button @click="importFromSource()" class="btn-primary">Retry with New Token</button>
        </div>
      </div>
    </div>
  </div>
</template>