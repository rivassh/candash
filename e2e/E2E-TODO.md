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
- [x] P2 — Candidates CRUD scenarios
  - Added @show, @update, @delete scenarios to `candidates.feature`
  - Added `I created a candidate with name {string}` Given step in `api-steps.ts`
  - Added PUT/DELETE request steps in `api-steps.ts`
  - Fixed mock server regex for `/profile` endpoint
- [x] P3 — Matching scenarios (search, show, save, remove)
  - Added @search, @save, @remove scenarios to `matches.feature`
  - Enhanced mock server with search query param and POST/DELETE for `/api/match/results`
  - Added `I created a match result with id {string}` Given step in `api-steps.ts`
  - Added PATCH request step in `api-steps.ts`
- [x] P4 — Fix DB configuration / standardize ports
  - Standardized `DB_PORT=5432` across all `.env` files (root `.env`, `.env.example`, `api/.env.example`, `.env.test`)
  - Removed duplicate `DB_PORT` entry in `api/.env.example` (was defined in both Ports and Database sections)
  - Created `.env.test` for isolated testing environment
  - Added `.env` protection rule to `.gitignore`

### 🔄 IN PROGRESS

### 📋 REMAINING (by priority)

#### 🔴 P1 — HIGHEST PRIORITY: Fix intermittent DB connection (race condition)
- **Status**: Complete — DB_PORT inconsistency fixed, retry logic added to step definitions
- **Next**: Run E2E suite 3x to confirm fix works
- **Related**: Auth me, Dashboard, JobPositions endpoints
- **Task**: `T1.1` — Stabilize DB connections (retry logic + startup delay)

#### 🔴 P2 — HIGH PRIORITY: Implement Candidates CRUD scenarios
- **Status**: Complete
- **Tasks**: All 7 E2E scenarios pass (@list, @create, @create-validation, @search, @show, @update, @delete)
- **Task**: `T1.2` — Implement Candidates CRUD scenarios

#### 🟡 P3 — MEDIUM PRIORITY: Add matching scenarios
- **Status**: Complete
- **Tasks**: All 8 matching scenarios pass (@run, @results, @show, @status, @search, @save, @remove)
- **Task**: `T1.3` — Add matching scenarios (search, show, save, remove)

#### 🟡 P4 — MEDIUM PRIORITY: Fix DB configuration / standardize ports
- **Status**: Complete
- **Tasks**: Standardized `DB_PORT=5432` across all `.env` files, created `.env.test`, added `.env` protection to `.gitignore`
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
2. **P5 — Browser E2E** — add Playwright browser scenarios for login, dashboard, candidates
3. **P6 — CI/CD** — implement GitHub Actions for automated E2E testing
4. **P7 — Align** — fix Laravel version mismatch in `composer.json`
5. **P8 — Clean** — remove remaining debug logs from `api-steps.ts`

## Notes

- All scenarios use `http://172.26.0.1:8085` as base URL
- Admin credentials from UserSeeder: `admin@talentmatch.local` / `admin123`
- HR credentials: `hr@talentmatch.local` / `hr123456`
- Playwright browser cache mounted from host: `~/.cache/ms-playwright/chromium-1228/`
- E2E tests run in Docker container with Node 20
- E2E tests target mock API at `http://localhost:8086` by default via `e2e/docker-compose.yml`
- `.env.test` created for isolated testing — never modify `.env` directly
