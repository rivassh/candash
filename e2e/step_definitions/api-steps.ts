import { Given, When, Then, DataTable, setWorldConstructor } from '@cucumber/cucumber'
import { CustomWorld } from '../support/world'
setWorldConstructor(CustomWorld)

const mockApiUrl = process.env.MOCK_API_URL ?? process.env.CANDASH_API ?? 'http://localhost:8086'

Given('the mock API server is ready', async function (this: CustomWorld) {
  this.apiBase = mockApiUrl
})

Given('I am authenticated for candidates with mock credentials', async function (this: CustomWorld) {
  this.token = 'mock-auth-token'
  this.apiBase = mockApiUrl
})

Given('the API is reachable at {string}', async function (this: CustomWorld, url: string) {
  this.apiBase = url
  const res = await fetch(`${url}/api/health`)
  if (!res.ok) throw new Error(`API not reachable at ${url}: ${res.status}`)
})

Given(
  'I am authenticated as {string} with password {string}',
  async function (this: CustomWorld, email: string, password: string) {
    const res = await fetch(`${this.apiBase}/api/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password }),
    })
    const body = await res.json()
    if (!res.ok) throw new Error(`Auth failed: ${JSON.stringify(body)}`)
    this.token = body.token
    this.lastResponse = { status: res.status, body }
  }
)

When('I send a POST request to {string} with:', async function (this: CustomWorld, path: string, data: DataTable) {
  const hash = data.hashes()[0]
  const res = await fetch(`${this.apiBase}${path}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(this.token ? { Authorization: `Bearer ${this.token}` } : {}),
    },
    body: JSON.stringify(hash),
  })
  const body = await res.json().catch(() => ({}))
  this.lastResponse = { status: res.status, body }
})

When('I send a POST request to {string} with body:', async function (this: CustomWorld, path: string, bodyStr: string) {
  const res = await fetch(`${this.apiBase}${path}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(this.token ? { Authorization: `Bearer ${this.token}` } : {}),
    },
    body: bodyStr.trim(),
  })
  this.lastResponse = { status: res.status, body: await res.json().catch(() => ({})) }
})

When('I send a POST request to {string}', async function (this: CustomWorld, path: string) {
  const res = await fetch(`${this.apiBase}${path}`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      ...(this.token ? { Authorization: `Bearer ${this.token}` } : {}),
    },
  })
  this.lastResponse = { status: res.status, body: await res.json().catch(() => ({})) }
})

When('I send a GET request to {string}', async function (this: CustomWorld, path: string) {
  const id = this.lastCandidateId ?? this.lastMatchResultId ?? this.lastJobPositionId ?? ''
  const resolvedPath = path.replace('{id}', String(id))
  const res = await fetch(`${this.apiBase}${resolvedPath}`, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      ...(this.token ? { Authorization: `Bearer ${this.token}` } : {}),
    },
  })
  this.lastResponse = { status: res.status, body: await res.json().catch(() => ({})) }
})

Then('the response status should be {int}', function (this: CustomWorld, status: number) {
  if (!this.lastResponse) throw new Error('No response recorded')
  if (this.lastResponse.status !== status) {
    throw new Error(`Expected status ${status} but got ${this.lastResponse.status}. Body: ${JSON.stringify(this.lastResponse.body)}`)
  }
})

Then('the response should contain {string}', function (this: CustomWorld, field: string) {
  if (!this.lastResponse) throw new Error('No response recorded')
  if (!(field in this.lastResponse.body)) {
    throw new Error(`Response missing field: "${field}". Body: ${JSON.stringify(this.lastResponse.body)}`)
  }
})

Then('the response should contain {string} with value {string}', function (this: CustomWorld, field: string, value: string) {
  if (!this.lastResponse) throw new Error('No response recorded')
  const actual = this.lastResponse.body[field]
  if (actual !== value) {
    throw new Error(`Field "${field}" expected "${value}" but got "${actual}"`)
  }
})

Then(
  'the response should contain {string} with nested fields {string}',
  function (this: CustomWorld, field: string, nested: string) {
    if (!this.lastResponse) throw new Error('No response recorded')
    const parts = nested.split(',').map((s) => s.trim())
    const obj = this.lastResponse.body[field]
    if (!obj) throw new Error(`Field "${field}" not found in body`)
    for (const p of parts) {
      if (!(p in obj)) throw new Error(`Nested field "${p}" not found in ${field}`)
    }
  }
)

Then(
  'the response should contain a user object with {string} and {string}',
  function (this: CustomWorld, field1: string, field2: string) {
    if (!this.lastResponse) throw new Error('No response recorded')
    const user = this.lastResponse.body.user ?? this.lastResponse.body
    if (!user) throw new Error('No user object in response')
    if (!(field1 in user)) throw new Error(`User object missing field: "${field1}"`)
    if (!(field2 in user)) throw new Error(`User object missing field: "${field2}"`)
  }
)

Then('the response should contain {string} as an array', function (this: CustomWorld, field: string) {
  if (!this.lastResponse) throw new Error('No response recorded')
  const val = this.lastResponse.body[field]
  if (!Array.isArray(val)) throw new Error(`Field "${field}" should be an array, got: ${typeof val}`)
})
