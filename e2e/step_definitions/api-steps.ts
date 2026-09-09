import { Given, When, Then, DataTable, setWorldConstructor } from '@cucumber/cucumber'
import { CustomWorld } from '../support/world'
setWorldConstructor(CustomWorld)

Given('the API is reachable at {string}', async function (this: CustomWorld, url: string) {
  this.apiBase = url
  console.log(`API base set to: ${url}`)
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
  console.log(`LOGIN status=${res.status} body:`, JSON.stringify(body).substring(0, 200))
  if (!res.ok) throw new Error(`Auth failed: ${JSON.stringify(body)}`)
  this.token = body.token
    this.lastResponse = { status: res.status, body }
    // Verify token is usable (Laravel DB may need a moment to propagate)
    let verify = await fetch(`${this.apiBase}/api/auth/me`, {
      headers: { Authorization: `Bearer ${this.token}`, Accept: 'application/json' },
    })
    for (let i = 0; i < 5 && !verify.ok; i++) {
      await new Promise((r) => setTimeout(r, 300))
      verify = await fetch(`${this.apiBase}/api/auth/me`, {
        headers: { Authorization: `Bearer ${this.token}`, Accept: 'application/json' },
      })
    }
  }
)

When('I send a POST request to {string} with:', async function (this: CustomWorld, path: string, data: DataTable) {
  const hash = data.hashes()[0]
  console.log(`POST ${path} body:`, JSON.stringify(hash))
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
  console.log(`POST ${path} status=${res.status}`, body)
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

When('I send a GET request to {string}', async function (this: CustomWorld, path: string) {
  const resolvedPath = path.replace('{id}', String(this.lastJobPositionId ?? ''))
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
    throw new Error(
      `Expected status ${status} but got ${this.lastResponse.status}. Body: ${JSON.stringify(this.lastResponse.body)}`
    )
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

Then('the response should contain {string} as an array', function (this: CustomWorld, field: string) {
  if (!this.lastResponse) throw new Error('No response recorded')
  const val = this.lastResponse.body[field]
  if (!Array.isArray(val)) throw new Error(`Field "${field}" should be an array, got: ${typeof val}`)
})