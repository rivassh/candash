import { World } from '@cucumber/cucumber'

export class CustomWorld extends World {
  apiBase: string
  token: string | null
  lastResponse: any
  lastJobPositionId: number | null
}
