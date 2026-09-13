import { Given } from '@cucumber/cucumber'
import { CustomWorld } from '../support/world'

async function fetchWithRetry(url: string, options: RequestInit, maxRetries = 3, delay = 2000): Promise<Response> {
  let lastError: Error | null = null
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      const res = await fetch(url, options)
      return res
    } catch (error) {
      lastError = error as Error
      if (attempt < maxRetries) {
        await new Promise((r) => setTimeout(r, delay))
      }
    }
  }
  throw lastError!
}

Given('I created a job position with title {string}', async function (this: CustomWorld, title: string) {
  const res = await fetchWithRetry(`${this.apiBase}/api/JobPositions`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      Authorization: `Bearer ${this.token}`,
    },
    body: JSON.stringify({
      title,
      department: 'E2E',
      level: 'mid',
      employment_type: 'full_time',
      min_experience_years: 1,
      description: 'Created by E2E test',
      required_skills: [],
    }),
  })
  const body = await res.json()
  if (!res.ok) throw new Error(`Failed to create position: ${JSON.stringify(body)}`)
  this.lastJobPositionId = body.id
  this.lastResponse = { status: res.status, body }
})