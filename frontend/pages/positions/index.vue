<script setup lang="ts">
definePageMeta({ layout: 'default' })

const api = useApi()
const router = useRouter()

const { data, pending, refresh } = await useAsyncData('positions', () =>
  api.get<any>('/job-positions')
)

const showModal = ref(false)
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
    await api.post('/job-positions', {
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
    alert(e.message)
  }
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
          </tr>
        </thead>
        <tbody>
          <tr v-for="pos in data?.data" :key="pos.id" class="table-row">
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
          </tr>
          <tr v-if="!data?.data?.length">
            <td colspan="7" class="px-4 py-8 text-center text-gray-400">موقعیتی یافت نشد.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 mx-4">
        <h2 class="text-lg font-bold mb-4">ثبت موقعیت شغلی جدید</h2>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">{{ error }}</div>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="label">عنوان موقعیت *</label>
              <input v-model="form.title" class="input" placeholder="مثال: Senior Backend Developer" required />
            </div>
            <div>
              <label class="label">دپارتمان *</label>
              <input v-model="form.department" class="input" placeholder="Engineering" required />
            </div>
            <div>
              <label class="label">سطح *</label>
              <select v-model="form.level" class="input">
                <option v-for="l in levels" :key="l" :value="l">{{ levelLabels[l] }}</option>
              </select>
            </div>
            <div>
              <label class="label">نوع استخدام *</label>
              <select v-model="form.employment_type" class="input">
                <option v-for="t in empTypes" :key="t" :value="t">{{ empLabels[t] }}</option>
              </select>
            </div>
            <div>
              <label class="label">حداقل سابقه (سال)</label>
              <input v-model.number="form.min_experience_years" type="number" min="0" class="input" />
            </div>
            <div class="col-span-2">
              <label class="label">الزامات تحصیلی</label>
              <input v-model="form.education_requirements" class="input" placeholder="مثال: کارشناسی مهندسی کامپیوتر" />
            </div>
            <div class="col-span-2">
              <label class="label">توضیحات</label>
              <textarea v-model="form.description" class="input" rows="3"></textarea>
            </div>
          </div>

          <!-- Required Skills -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="label mb-0">مهارت‌های الزامی</label>
              <button type="button" @click="addSkill" class="text-xs text-primary-600 hover:underline">+ افزودن</button>
            </div>
            <div v-for="(skill, i) in form.required_skills" :key="i" class="flex gap-2 mb-2 items-center">
              <input v-model="skill.name" class="input flex-1" placeholder="نام مهارت (مثال: PHP)" />
              <input v-model.number="skill.weight" type="number" min="1" max="10" class="input w-16" title="وزن" placeholder="وزن" />
              <input v-model.number="skill.min_years" type="number" min="0" class="input w-20" title="حداقل سال" placeholder="حداقل سال" />
              <button type="button" @click="removeSkill(i)" class="text-danger-500 hover:text-danger-700 text-sm">✕</button>
            </div>
          </div>

          <!-- Preferred Skills -->
          <div>
            <label class="label">مهارت‌های ترجیحی</label>
            <div class="flex gap-2">
              <input @keydown.enter.prevent="addPreferred(($event.target as HTMLInputElement).value); ($event.target as HTMLInputElement).value = ''"
                class="input flex-1" placeholder="نام مهارت + Enter" />
            </div>
            <div class="flex flex-wrap gap-1 mt-2">
              <span v-for="s in form.preferred_skills" :key="s"
                class="badge-info flex items-center gap-1">
                {{ s }}
                <button type="button" @click="form.preferred_skills = form.preferred_skills.filter(x => x !== s)" class="hover:text-red-600">✕</button>
              </span>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="showModal = false" class="btn-secondary">انصراف</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'در حال ذخیره...' : 'ثبت موقعیت' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>