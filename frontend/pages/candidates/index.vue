<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

const api = useApi()
const router = useRouter()

const { data, pending, refresh } = await useAsyncData('candidates', () =>
  api.get<any>('/candidates', { paginate: 'true' })
)

const showModal = ref(false)
const form = ref({ name: '', email: '', phone: '', linkedin_url: '', summary: '' })
const saving = ref(false)
const error = ref('')

async function submit() {
  saving.value = true
  error.value = ''
  try {
    const result = await api.post<any>('/candidates', form.value)
    showModal.value = false
    form.value = { name: '', email: '', phone: '', linkedin_url: '', summary: '' }
    router.push(`/candidates/${result.id}`)
  } catch (e: any) {
    error.value = e.message
  } finally {
    saving.value = false
  }
}

const statusColors: Record<string, string> = {
  new: 'badge-info', in_review: 'badge-warning', shortlisted: 'badge-success', rejected: 'badge-danger', hired: 'badge-success',
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800">کاندیداها</h1>
      <button @click="showModal = true" class="btn-primary">+ کاندیدا جدید</button>
    </div>

    <div v-if="pending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="card overflow-hidden p-0">
      <table class="w-full text-sm">
        <thead>
          <tr class="table-header">
            <th class="px-4 py-3 text-right">نام</th>
            <th class="px-4 py-3 text-right">پست الکترونیکی</th>
            <th class="px-4 py-3 text-right">شماره تماس</th>
            <th class="px-4 py-3 text-right">مهارت‌ها</th>
            <th class="px-4 py-3 text-right">وضعیت</th>
            <th class="px-4 py-3 text-right">رزومه</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in data?.data" :key="c.id"
            class="table-row cursor-pointer"
            @click="router.push(`/candidates/${c.id}`)"
          >
            <td class="px-4 py-3 font-medium">{{ c.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ c.email || '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ c.phone || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap gap-1">
                <span v-for="s in (c.skills || []).slice(0, 3)" :key="s.id" class="badge-info text-xs">
                  {{ s.name }}
                </span>
                <span v-if="(c.skills || []).length > 3" class="badge-gray text-xs">
                  +{{ c.skills.length - 3 }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3">
              <span :class="statusColors[c.status] || 'badge-gray'">
                {{ { new: 'جدید', in_review: 'در حال بررسی', shortlisted: 'تایید اولیه', rejected: 'رد شده', hired: 'استخدام شده' }[c.status] || c.status }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span v-if="c.latest_resume" class="badge-success text-xs">✓</span>
              <span v-else class="text-gray-300 text-xs">—</span>
            </td>
          </tr>
          <tr v-if="!data?.data?.length">
            <td colspan="6" class="px-4 py-8 text-center text-gray-400">کاندیدایی یافت نشد.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="data?.meta" class="flex items-center justify-between p-4 border-t">
        <p class="text-xs text-gray-500">
          صفحه {{ data.meta.current_page }} از {{ data.meta.last_page }} — مجموع {{ data.meta.total }}
        </p>
        <div class="flex gap-1">
          <button @click="refresh()" class="btn-secondary text-xs">بروزرسانی</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 mx-4">
        <h2 class="text-lg font-bold mb-4">ثبت کاندیدای جدید</h2>
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">{{ error }}</div>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="label">نام کامل *</label>
            <input v-model="form.name" class="input" placeholder="نام و نام خانوادگی" required />
          </div>
          <div>
            <label class="label">پست الکترونیکی</label>
            <input v-model="form.email" type="email" class="input" placeholder="example@domain.com" />
          </div>
          <div>
            <label class="label">شماره تماس</label>
            <input v-model="form.phone" class="input" placeholder="+98 ..." />
          </div>
          <div>
            <label class="label">لینکدین URL</label>
            <input v-model="form.linkedin_url" type="url" class="input" placeholder="https://linkedin.com/in/..." />
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="showModal = false" class="btn-secondary">انصراف</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'در حال ذخیره...' : 'ثبت کاندیدا' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>