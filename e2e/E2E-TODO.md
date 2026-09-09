# E2E Test TODO List

## Phase 8: Interim Status (as of $(date +%Y-%m-%d))

### ✅ COMPLETED
- [x] Login payload nesting bug fix (both frontend and backend)
  - Fixed `api-steps.ts` data table parsing: switched from `data.raw().slice(1)` to `data.hashes()[0]`
  - Fixed `pages/index.vue:15` frontend call: `authStore.login(email.value, password.value)` (two positional args)
- [x] Container migration to ymls project
  - Upgraded to `ymls-api` and `ymls-frontend` images
  - Fixed DB credentials via `ymls/candash.env`
- [x] Configuration cache fixes
  - Cleared Laravel config cache (`php artisan config:clear`)
  - Cleared route cache (`route:clear`, `route:cache`)
- [x] API route fix
  - Corrected `positions-steps.ts`: changed `/api/job-positions` to `/api/JobPositions`
- [x] Basic E2E suite (Last Full Run)
  - Auth login (admin@talentmatch.local → 200)
  - Auth login-fail (wrong password → 422)
  - Auth me (authenticated → 200)
  - Auth logout (authenticated → 200)
  - Dashboard summary (authenticated → 200)
  - JobPositions list (`/api/JobPositions` → 200)
  - JobPositions create (→ 201)
  - JobPositions create-validation (`{"title":""}` → 422)
  - JobPositions show (fixed route → 200)
- [x] Dependencies and environment
  - Added `node_modules/` to `.gitignore`
  - Prepared E2E test directory for commit
- [x] Intermittent DB connection investigation
  - Added retry logic to step definitions

### 🔄 IN PROGRESS

### 📋 REMAINING

#### 1. Fix intermittent DB connection (race condition) - HIGHEST PRIORITY
- **Status**: Retry logic added; still experiencing failures
- **Next**: Check DB container readiness, possibly increase DB startup delay
- **Related**: Auth me, Dashboard, JobPositions endpoints failing intermittently

#### 2. Add candidates CRUD scenarios
- **Status**: Not yet implemented
- **Tasks**:
  - GET `/api/candidates` (list)
  - GET `/api/candidates/{id}` (show)
  - POST `/api/candidates` (create)
  - PUT `/api/candidates/{id}` (update)
  - DELETE `/api/candidates/{id}` (delete)

#### 3. Add matching scenarios
- **Status**: Not yet implemented
- **Tasks**:
  - POST `/api/match/search` (search with filters)
  - GET `/api/match/{id}` (match results)
  - POST `/api/match/save` (save candidate match)
  - DELETE `/api/match/{id}` (remove match)

#### 4. Add browser-based E2E scenarios via Playwright
- **Status**: Not yet implemented
- **Tasks**:
  - Login to frontend app via browser
  - Navigate dashboard, candidates, positions
  - Test form interactions, search, filters
  - Verify UI state and navigation

#### 5. Add CI integration (GitHub Actions)
- **Status**: Not yet implemented
- **Tasks**:
  - Create `.github/workflows/e2e.yml`
  - Configure matrix for different Docker images
  - Set up Playwright browsers
  - Generate test reports and artifacts
  - Schedule runs on PRs and pushes to main

#### 6. Remove debug logging from api-steps.ts
- **Status**: Partially done
- **Remaining**: Remove any remaining `console.log` statements
- **Verify**: Clean E2E logs for production

#### 7. Add `node_modules/` to `.gitignore` (already done but double-check)
- **Status**: Done
- **Verify**: Ensure `.gitignore` includes `node_modules/` in e2e directory

## Action Items for Next Sprint

1. **Priority**: Stabilize DB connection - run E2E suite 3x to confirm retry logic works
2. **Prepare**: Set up candidates CRUD scenario tests
3. **Document**: Update API contract documentation if new endpoints added
4. **Clean**: Final cleanup of any remaining debug logs
5. **CI/CD**: Implement GitHub Actions for automated E2E testing

## Notes

- All scenarios use `http://172.26.0.1:8085` as base URL
- Admin credentials from UserSeeder: `admin@talentmatch.local` / `admin123`
- HR credentials: `hr@talentmatch.local` / `hr123456`
- Playwright browser cache mounted from host: `~/.cache/ms-playwright/chromium-1228/`
- E2E tests run in Docker container with Node 20
