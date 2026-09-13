<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'admin' })

const api = useApi()
const { data, pending, refresh } = await useAsyncData('jobvision-credentials', () =>
  api.get<any[]>('/jobvision-credentials')
)

const form = ref({
  username: '',
  password: '',
  cookie: '',
  api_url: 'https://employerapi.jobvision.ir',
  account_url: 'https://account.jobvision.ir',
  job_post_ids: [] as number[],
  expire_at: '',
  is_active: false,
})
const editingId = ref<number | null>(null)
const showingForm = ref(false)
const saving = ref(false)
const errorMsg = ref('')

const browserLoginOpen = ref(false)
const browserSessionId = ref('')
const browserStatus = ref('idle')
const browserScreenshot = ref('')
const browserError = ref('')
let pollTimer: number | null = null

function resetForm() {
  form.value = {
    username: '',
    password: '',
    cookie: '',
    api_url: 'https://employerapi.jobvision.ir',
    account_url: 'https://account.jobvision.ir',
    job_post_ids: [],
    expire_at: '',
    is_active: false,
  }
  editingId.value = null
  errorMsg.value = ''
}

function openCreate() {
  resetForm()
  showingForm.value = true
}

function openEdit(cred: any) {
  form.value = {
    username: cred.username || '',
    password: '',
    cookie: cred.cookie || '',
    api_url: cred.api_url || '',
    account_url: cred.account_url || '',
    job_post_ids: cred.job_post_ids || [],
    expire_at: cred.expire_at ? cred.expire_at.split('T')[0] : '',
    is_active: cred.is_active || false,
  }
  editingId.value = cred.id
  showingForm.value = true
}

const jobPostIds = computed({
  get: () => form.value.job_post_ids.join(', '),
  set: (value: string) => {
    form.value.job_post_ids = value.split(',').map(v => Number(v.trim())).filter(v => !Number.isNaN(v))
  },
})

async function submit() {
  saving.value = true
  errorMsg.value = ''
  try {
    const body = {
      username: form.value.username || null,
      password: form.value.password || null,
      cookie: form.value.cookie || null,
      api_url: form.value.api_url,
      account_url: form.value.account_url,
      job_post_ids: form.value.job_post_ids,
      expire_at: form.value.expire_at || null,
      is_active: form.value.is_active,
    }
    if (editingId.value) {
      await api.put(`/jobvision-credentials/${editingId.value}`, body)
    } else {
      await api.post('/jobvision-credentials', body)
    }
    showingForm.value = false
    await refresh()
  } catch (e: any) {
    errorMsg.value = e.message
  } finally {
    saving.value = false
  }
}

async function activate(cred: any) {
  try {
    await api.post(`/jobvision-credentials/${cred.id}/activate`)
    await refresh()
  } catch (e: any) {
    alert(e.message)
  }
}

async function remove(id: number) {
  if (!confirm('آیا مطمئن هستید؟')) return
  try {
    await api.del(`/jobvision-credentials/${id}`)
    await refresh()
  } catch (e: any) {
    alert(e.message)
  }
}

async function startBrowserLogin() {
  browserLoginOpen.value = true
  browserStatus.value = 'starting'
  browserScreenshot.value = ''
  browserError.value = ''
  try {
    const res = await api.post<any>('/admin/jobvision-browser-login', {
      account_url: form.value.account_url,
    })
    browserSessionId.value = res.session_id
    browserStatus.value = 'waiting_for_login'
    pollBrowserStatus()
  } catch (e: any) {
    browserError.value = e.message
    browserStatus.value = 'error'
  }
}

function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
}

async function pollBrowserStatus() {
  stopPolling()
  pollTimer = window.setInterval(async () => {
    try {
      const res = await api.get<any>(`/admin/jobvision-browser-login/status?session_id=${browserSessionId.value}`)
      browserStatus.value = res.status
      if (res.cookies) { browserStatus.value = 'complete'; stopPolling() }
    } catch {}
  }, 2000)
  refreshScreenshot()
}

async function refreshScreenshot() {
  if (!browserSessionId.value) return
  try {
    const res = await api.get<any>(`/admin/jobvision-browser-login/screenshot?session_id=${browserSessionId.value}`)
    browserScreenshot.value = `data:image/png;base64,${res.data}`
  } catch {}
}

async function cancelBrowserLogin() {
  stopPolling()
  if (browserSessionId.value) {
    try { await api.del(`/admin/jobvision-browser-login/${browserSessionId.value}`) } catch {}
  }
  browserLoginOpen.value = false
  browserStatus.value = 'idle'
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800">مدیریت اطلاعات احراز هویت JobVision</h1>
      <div class="flex gap-2">
        <button @click="startBrowserLogin()" class="btn-secondary" :disabled="browserLoginOpen">
          🌐 ورود با مرورگر
        </button>
        <button @click="openCreate()" class="btn-primary">+ اطلاعات جدید</button>
      </div>
    </div>

    <div v-if="pending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="card overflow-hidden p-0">
      <table class="w-full text-sm">
        <thead>
          <tr class="table-header">
            <th class="px-4 py-3 text-right">شماره</th>
            <th class="px-4 py-3 text-right">نام کاربری</th>
            <th class="px-4 py-3 text-right">API URL</th>
            <th class="px-4 py-3 text-right">کوکی</th>
            <th class="px-4 py-3 text-right">تاریخ انقضا</th>
            <th class="px-4 py-3 text-right">فعال</th>
            <th class="px-4 py-3 text-right">عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cred in data" :key="cred.id" class="table-row">
            <td class="px-4 py-3">{{ cred.id }}</td>
            <td class="px-4 py-3">{{ cred.username || '—' }}</td>
            <td class="px-4 py-3 text-xs">{{ cred.api_url }}</td>
            <td class="px-4 py-3">
              <span v-if="cred.cookie" class="text-green-600">✔</span>
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="px-4 py-3">{{ cred.expire_at ? cred.expire_at.split('T')[0] : '—' }}</td>
            <td class="px-4 py-3">
              <span :class="cred.is_active ? 'badge-success' : 'badge-gray'">
                {{ cred.is_active ? 'فعال' : 'غیرفعال' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button @click="openEdit(cred)" class="btn-secondary text-xs">✏️</button>
              <button @click="activate(cred)" class="btn-secondary text-xs">
                {{ cred.is_active ? '🔴' : '🟢' }}
              </button>
              <button @click="remove(cred.id)" class="btn-danger text-xs">🗑️</button>
            </td>
          </tr>
          <tr v-if="!data?.length">
            <td colspan="7" class="px-4 py-8 text-center text-gray-400">هیچ credential ثبت نشده است.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg">
      {{ errorMsg }}
    </div>

    <div v-if="showingForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 mx-4">
        <h2 class="text-lg font-bold mb-4">{{ editingId ? 'ویرایش' : 'ایجاد' }}</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">نام کاربری</label>
              <input v-model="form.username" class="input" placeholder="sharifinv@mapsa.com" />
            </div>
            <div>
              <label class="label">رمز عبور</label>
              <input v-model="form.password" type="password" class="input" placeholder="••••••••" />
            </div>
          </div>

          <div>
            <label class="label">کوکی (اختیاری)</label>
            <textarea v-model="form.cookie" class="input" rows="2" placeholder="__smid=...; SERVERID=..."></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">API URL</label>
              <input v-model="form.api_url" type="url" class="input" />
            </div>
            <div>
              <label class="label">Account URL</label>
              <input v-model="form.account_url" type="url" class="input" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">تاریخ انقضا</label>
              <input v-model="form.expire_at" type="date" class="input" />
            </div>
          <div>
            <label class="label">Job Post IDs</label>
            <input v-model="jobPostIds" class="input" placeholder="1503606, 1426362" />
          </div>
          </div>

          <div>
            <label class="label">فعال</label>
            <input v-model="form.is_active" type="checkbox" class="checkbox mt-2" />
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="showingForm = false" class="btn-secondary">انصراف</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'در حال ذخیره...' : editingId ? 'به‌روزرسانی' : 'ایجاد' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="browserLoginOpen" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-hidden p-0 mx-4 flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h2 class="text-lg font-bold">ورود با مرورگر — JobVision</h2>
          <button @click="cancelBrowserLogin" class="btn-secondary text-xs">✕ بستن</button>
        </div>

        <div class="px-6 py-3 bg-gray-50 flex items-center gap-3 text-sm">
          <span :class="{
            'badge-gray': browserStatus === 'idle' || browserStatus === 'starting',
            'badge-info': browserStatus === 'waiting_for_login',
            'badge-success': browserStatus === 'complete',
            'badge-danger': browserStatus === 'error' || browserStatus === 'timeout',
          }">
            {{ { idle: 'آماده', starting: 'در حال راه‌اندازی...', waiting_for_login: 'در انتظار ورود به مرورگر', complete: 'ورود موفقیت‌آمیز!', error: 'خطا', timeout: 'زمان‌بندی تمام شد' }[browserStatus] || browserStatus }}
          </span>
          <span v-if="browserStatus === 'complete'" class="text-green-700">کوکی‌ها به‌صورت خودکار ذخیره شدند.</span>
        </div>

        <div class="flex-1 overflow-hidden relative bg-black" style="min-height: 400px;">
          <img v-if="browserScreenshot" :src="browserScreenshot" class="w-full h-full object-contain" alt="Browser" />
          <div v-else class="absolute inset-0 flex items-center justify-center text-white">
            <span>{{ browserStatus === 'complete' ? 'تمام شد' : 'در حال بارگذاری مرورگر...' }}</span>
          </div>
        </div>

        <div class="px-6 py-4 border-t flex justify-end gap-2">
          <button v-if="browserStatus !== 'complete'" @click="cancelBrowserLogin" class="btn-danger">لغو</button>
          <button v-else @click="cancelBrowserLogin" class="btn-primary">بستن</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.checkbox {
  display: flex;
  align-items: center;
}
</style>