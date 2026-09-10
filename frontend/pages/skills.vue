<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

const api = useApi()
const { data, pending, refresh } = await useAsyncData('skills', () =>
  api.get<any>('/skills')
)

const showModal = ref(false)
const form = ref({ name: '', category: '', aliases: [] })
const saving = ref(false)
const aliasInput = ref('')

async function submit() {
  saving.value = true
  try {
    await api.post('/skills', { ...form.value, aliases: form.value.aliases.filter(Boolean) })
    showModal.value = false
    form.value = { name: '', category: '', aliases: [] }
    await refresh()
  } catch (e: any) {
    alert(e.message)
  } finally {
    saving.value = false
  }
}

function addAlias() {
  if (aliasInput.value && !form.value.aliases.includes(aliasInput.value)) {
    form.value.aliases.push(aliasInput.value.trim())
    aliasInput.value = ''
  }
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-800">دیکشنری مهارت‌ها</h1>
      <button @click="showModal = true" class="btn-primary">+ مهارت جدید</button>
    </div>

    <div v-if="pending" class="text-center py-12 text-gray-400">در حال بارگذاری...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
      <div v-for="skill in data?.data" :key="skill.id" class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-gray-800">{{ skill.name }}</p>
            <p class="text-xs text-gray-400">{{ skill.category || 'بدون دسته' }}</p>
          </div>
          <span v-if="!skill.is_active" class="badge-danger text-xs">غیرفعال</span>
        </div>
        <div v-if="skill.aliases?.length" class="flex flex-wrap gap-1 mt-2">
          <span v-for="a in skill.aliases" :key="a" class="badge-gray text-xs">{{ a }}</span>
        </div>
      </div>
      <div v-if="!data?.data?.length" class="col-span-full text-center py-12 text-gray-400">
        مهارتی یافت نشد.
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 mx-4">
        <h2 class="text-lg font-bold mb-4">ثبت مهارت جدید</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="label">نام اصلی *</label>
            <input v-model="form.name" class="input" required />
          </div>
          <div>
            <label class="label">دسته‌بندی</label>
            <input v-model="form.category" class="input" placeholder="Backend, Frontend, DevOps, ..." />
          </div>
          <div>
            <label class="label"> مترادف‌ها</label>
            <div class="flex gap-2">
              <input v-model="aliasInput" @keydown.enter.prevent="addAlias" class="input flex-1"
                placeholder="نام مترادف + Enter" />
            </div>
            <div class="flex flex-wrap gap-1 mt-2">
              <span v-for="a in form.value.aliases" :key="a"
                class="badge-info flex items-center gap-1">
                {{ a }}
              </span>
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="showModal = false" class="btn-secondary">انصراف</button>
            <button type="submit" class="btn-primary">ثبت</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>