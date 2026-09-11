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

### 📋 REMAINING (by priority)

#### 🔴 P1 — HIGHEST PRIORITY: Fix intermittent DB connection (race condition)
- **Status**: Retry logic added; still experiencing failures
- **Next**: Check DB container readiness, possibly increase DB startup delay
- **Related**: Auth me, Dashboard, JobPositions endpoints failing intermittently
- **Task**: `T1.1` — Stabilize DB connections (retry logic + startup delay)

#### 🔴 P2 — HIGH PRIORITY: Implement Candidates CRUD scenarios
- **Status**: Not yet implemented
- **Tasks**:
  - GET `/api/candidates` (list)
  - GET `/api/candidates/{id}` (show)
  - POST `/api/candidates` (create)
  - PUT `/api/candidates/{id}` (update)
  - DELETE `/api/candidates/{id}` (delete)
- **Task**: `T1.2` — Implement Candidates CRUD scenarios

#### 🟡 P3 — MEDIUM PRIORITY: Add matching scenarios
- **Status**: Not yet implemented
- **Tasks**:
  - POST `/api/match/search` (search with filters)
  - GET `/api/match/{id}` (match results)
  - POST `/api/match/save` (save candidate match)
  - DELETE `/api/match/{id}` (remove match)
- **Task**: `T1.3` — Add matching scenarios (search, show, save, remove)

#### 🟡 P4 — MEDIUM PRIORITY: Fix DB configuration / standardize ports
- **Status**: DB port inconsistencies (5432 vs 5433 vs 5434)
- **Next**: Standardize `.env` values, align with env.example
- **Related**: Laravel version mismatch, missing phpunit.xml
- **Task**: `T1.4` — Fix DB configuration standardize ports

#### 🟢 P5 — LOW PRIORITY: Add browser-based E2E scenarios via Playwright
- **Status**: Not yet implemented
- **Tasks**:
  - Login to frontend app via browser
  - Navigate dashboard, candidates, positions
  - Test form interactions, search, filters
  - Verify UI state and navigation

#### 🟢 P6 — LOW PRIORITY: Add CI integration (GitHub Actions)
- **Status**: Not yet implemented
- **Tasks**:
  - Create `.github/workflows/e2e.yml`
  - Configure matrix for different Docker images
  - Set up Playwright browsers
  - Generate test reports and artifacts
  - Schedule runs on PRs and pushes to main
- **Task**: `T1.5` — Set up CI integration (GitHub Actions e2e tests)

#### ⚪ P7 — LOW PRIORITY: Align Laravel version in composer.json
- **Status**: `api/composer.json` requires `^13.0` but README documents Laravel 11
- **Next**: Either align composer.json with installed version or update documentation
- **Task**: `T1.6` — Align Laravel version in composer.json with installed version

#### ⚪ P8 — LOW PRIORITY: Remove debug logging from api-steps.ts
- **Status**: Partially done
- **Remaining**: Remove any remaining `console.log` statements
- **Verify**: Clean E2E logs for production

#### ⚪ P9 — LOW PRIORITY: Add `node_modules/` to `.gitignore` (already done, double-check)
- **Status**: Done
- **Verify**: Ensure `.gitignore` includes `node_modules/` in e2e directory

## Action Items for Next Sprint (by priority)

1. **P1 — Stabilize DB connection** — run E2E suite 3x to confirm retry logic works
2. **P2 — Prepare** — set up candidates CRUD scenario tests
3. **P3 — Implement** — add matching scenarios (search, show, save, remove)
4. **P4 — Fix** — standardize DB ports and environment configuration
5. **P5 — Clean** — remove remaining debug logs from `api-steps.ts`
6. **P6 — Document** — update API contract documentation if new endpoints added
7. **P7 — CI/CD** — implement GitHub Actions for automated E2E testing
8. **P8 — Align** — fix Laravel version mismatch in `composer.json`

## Notes

- All scenarios use `http://172.26.0.1:8085` as base URL
- Admin credentials from UserSeeder: `admin@talentmatch.local` / `admin123`
- HR credentials: `hr@talentmatch.local` / `hr123456`
- Playwright browser cache mounted from host: `~/.cache/ms-playwright/chromium-1228/`
- E2E tests run in Docker container with Node 20
