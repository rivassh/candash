<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'admin' })

const api = useApi()

const providers = ref([])
const selectedProvider = ref('')
const schemaFields = ref([])

const form = ref({})
const editingId = ref<number | null>(null)
const showingForm = ref(false)
const saving = ref(false)
const errorMsg = ref('')

const providersLoaded = ref(false)

const { data, pending, refresh } = await useAsyncData('credentials', () =>
  api.get<any[]>('/job-source-credentials')
)

const credentialList = computed(() => {
  const d: any = data.value
  if (!d) return []
  if (Array.isArray(d)) return d
  if (Array.isArray(d?.data)) return d.data
  return []
})

onMounted(async () => {
  const res = await api.get('/job-source-credentials/providers')
  providers.value = res.providers || []
  providersLoaded.value = true
})

function resetForm() {
  form.value = {}
  editingId.value = null
  selectedProvider.value = ''
  schemaFields.value = []
  errorMsg.value = ''
}

function openCreate() {
  resetForm()
  showingForm.value = true
}

function openEdit(cred: any) {
  const config = cred.config || {}
  form.value = { ...config, is_active: cred.is_active }
  editingId.value = cred.id
  selectedProvider.value = cred.provider || ''
  const provider = providers.value.find((p: any) => p.key === cred.provider)
  if (provider) {
    schemaFields.value = provider.fields || []
  }
  showingForm.value = true
}

function selectProvider(providerKey: string) {
  selectedProvider.value = providerKey
  const provider = providers.value.find((p: any) => p.key === providerKey)
  if (provider) {
    schemaFields.value = provider.fields || []
    const defaults: Record<string, any> = {}
    for (const [field, def] of Object.entries(provider.fields || {})) {
      defaults[field] = def.default ?? (def.type === 'boolean' ? false : '')
    }
    form.value = defaults
  }
}

async function submit() {
  saving.value = true
  errorMsg.value = ''
  try {
    const body = {
      provider: selectedProvider.value,
      ...form.value,
    }
    if (editingId.value) {
      await api.put(`/job-source-credentials/${editingId.value}`, body)
    } else {
      await api.post('/job-source-credentials', body)
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
    await api.post(`/job-source-credentials/${cred.id}/activate`, {})
    await refresh()
  } catch (e: any) {
    alert(e.message)
  }
}

async function remove(cred: any) {
  if (!confirm('Are you sure you want to delete this credential?')) return
  try {
    await api.delete(`/job-source-credentials/${cred.id}`)
    await refresh()
  } catch (e: any) {
    alert(e.message)
  }
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Credentials Management</h1>

    <div class="flex items-center justify-between mb-4">
      <button v-if="!showingForm" @click="openCreate" class="btn-primary">
        + Add New Credential
      </button>
    </div>

    <div v-if="showingForm" class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">{{ editingId ? 'Edit' : 'Add New' }} Credential</h2>

      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Provider</label>
        <select v-model="selectedProvider" @change="selectProvider(selectedProvider)" class="input input-bordered w-full">
          <option value="">Select a provider...</option>
          <option v-for="p in providers" :key="p.key" :value="p.key">
            {{ p.name }}
          </option>
        </select>
      </div>

      <form v-if="selectedProvider && schemaFields.length" @submit.prevent="submit" class="space-y-4">
        <template v-for="(def, field) in schemaFields" :key="field">
          <div v-if="def.type === 'boolean'">
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="form[field]" class="checkbox" />
              <span class="text-sm font-medium">{{ def.label || field }}</span>
            </label>
          </div>
          <div v-else>
            <label class="block text-sm font-medium mb-1">{{ def.label || field }}</label>
            <input
              :type="def.type === 'password' ? 'password' : def.type === 'url' ? 'url' : def.type === 'email' ? 'email' : 'text'"
              v-model="form[field]"
              class="input input-bordered w-full"
              :placeholder="def.label || field"
              :required="def.required"
            />
          </div>
        </template>

        <div class="flex justify-end gap-3">
          <button type="button" @click="showingForm.value = false" class="btn btn-outline">Cancel</button>
          <button type="submit" :disabled="saving" class="btn btn-primary">
            {{ saving ? 'Saving...' : editingId ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>

    <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg">
      {{ errorMsg }}
    </div>

    <div v-else>
      <div v-if="pending" class="text-center py-12 text-gray-400">Loading credentials...</div>

      <div v-else>
        <div v-if="credentialList.value.length === 0" class="text-center py-8">
          <p class="text-gray-500">No credentials found. Click "Add New Credential" to get started.</p>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="cred in credentialList.value" :key="cred.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ cred.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ cred.provider || '—' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ (cred.config && cred.config.username) || (cred.username) || '—' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', cred.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                  {{ cred.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="openEdit(cred)" class="text-blue-600 hover:text-blue-900">Edit</button>
                  <button @click="activate(cred)" :disabled="cred.is_active" class="text-green-600 hover:text-green-900">Activate</button>
                  <button @click="remove(cred)" class="text-red-600 hover:text-red-900">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>