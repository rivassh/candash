import { Given, When } from '@cucumber/cucumber'
import { CustomWorld } from '../support/world'

Given('I created a match result with id {string}', async function (this: CustomWorld, id: string) {
  this.lastMatchResultId = id
  this.lastResponse = {
    status: 200,
    body: {
      id,
      candidateId: 'candidate-1',
      jobPositionId: 'job-position-1',
      totalScore: 85,
      status: 'shortlisted',
    },
  }
})

When('I send a PATCH request to {string} with body:', async function (this: CustomWorld, path: string, bodyStr: string) {
  const res = await fetch(`${this.apiBase}${path}`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(this.token ? { Authorization: `Bearer ${this.token}` } : {}),
    },
    body: bodyStr.trim(),
  })
  const body = await res.json().catch(() => ({}))
  this.lastResponse = { status: res.status, body }
})