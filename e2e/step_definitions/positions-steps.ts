import { Given } from '@cucumber/cucumber'
import { CustomWorld } from '../support/world'

Given('I created a job position with title {string}', async function (this: CustomWorld, title: string) {
  const res = await fetch(`${this.apiBase}/api/JobPositions`, {
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