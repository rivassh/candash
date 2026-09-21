# RICE Remediation Plan — TalentMatch Codebase Review

RICE = (Reach × Impact × Confidence) / Effort

## Priority Order

| Rank | ID | Finding | R | I | C | Effort | Score | Domain |
|------|----|---------|---|---|---|--------|-------|--------|
| 1 | T11 | Register missing route `api/job-positions/import-from-source` | 50 | 2 | 1.0 | 0.1d | **1000** | Backend |
| 2 | T6 | Broken search pagination (search() resets page to 1) | 80 | 2 | 0.9 | 0.5d | **288** | Frontend |
| 3 | T7 | Unsanitized v-html XSS in application resume view | 60 | 2 | 0.9 | 0.5d | **216** | Frontend |
| 4 | T5 | Remove hardcoded secrets (api/.env APP_KEY, docker-compose DB password) | 100 | 3 | 0.95 | 1.5d | **190** | Ops/Security |
| 5 | T9 | Remove hardcoded demo credentials from login.vue | 100 | 1 | 0.9 | 0.5d | **180** | Frontend |
| 6 | T10 | Add rate limiting to auth endpoints | 100 | 2 | 0.85 | 1d | **170** | Security |
| 7 | T3 | Auth bypass risk in AuthController | 50 | 3 | 0.9 | 1d | **135** | Security |
| 8 | T4 | JWT in localStorage → httpOnly cookie | 100 | 3 | 0.85 | 2d | **127.5** | Security |
| 9 | T13 | Search results expose candidate emails/phones | 60 | 1 | 0.9 | 0.5d | **108** | Security |
| 10 | T16 | Duplicate fetchWithRetry + frontend API casing inconsistency | 30 | 1 | 0.85 | 0.5d | **102** | Frontend |
| 11 | T17 | Logs expose Authorization header in JobVisionCrawler.php:242 | 30 | 2 | 0.8 | 0.5d | **96** | Backend |
| 12 | T8 | Admin credentials page wrong API response shape | 20 | 2 | 0.95 | 0.5d | **76** | Frontend |
| 13 | T2 | SQLi + IDOR in JobSourceCredentialController | 50 | 3 | 0.9 | 2d | **67.5** | Security |
| 14 | T12 | File upload MIME type verification in CandidateController | 40 | 2 | 0.8 | 1d | **64** | Security |
| 15 | T14 | Contract drift (jobsource.yaml vs actual API routes) | 20 | 2 | 0.7 | 1d | **28** | Ops |
| 16 | T15 | E2E tests reference non-existent endpoints + no CI/CD pipeline | 50 | 2 | 0.7 | 3d | **23.3** | Ops/Dev |

## Task Details

### T11 — Register missing route (1000)
- Controller: `api/app/Http/Controllers/Api/JobPositionController.php:67` — `importFromSource()` exists
- Fix: Add `Route::post('job-positions/import-from-source', [JobPositionController::class, 'importFromSource'])->middleware('auth:sanctum');` to `api/routes/api.php`

### T6 — Broken search pagination (288)
- File: `frontend/pages/search.vue:93-103`
- Issue: `nextPage()` increments page then calls `search()` which resets page to 1
- Fix: Pass page as parameter to `search()` or separate pagination from search

### T7 — XSS via v-html (216)
- File: `frontend/pages/positions/[id]/applications/[applicationId].vue:73`
- Fix: Sanitize with DOMPurify or replace with `v-text`

### T5 — Hardcoded secrets (190)
- Files: `api/.env`, `docker-compose.yml:70`
- Fix: Move to `.env.example` template, add `.gitignore` rules, use Docker secrets

### T9 — Demo credentials (180)
- File: `frontend/pages/login.vue:53-56`
- Fix: Remove hardcoded admin@talentmatch.local / hr@talentmatch.local credentials

### T10 — Rate limiting (170)
- Fix: Add Laravel throttle middleware to auth routes in `api/routes/api.php`

### T3 — Auth bypass (135)
- File: `api/app/Http/Controllers/Api/AuthController.php:21-25`
- Fix: Review auth logic for bypass vulnerability

### T4 — JWT → httpOnly cookie (127.5)
- File: `frontend/stores/auth.ts:29-40`
- Fix: Store JWT in httpOnly secure cookie via API response, not localStorage

### T13 — PII exposure (108)
- Fix: Use field projection in Meilisearch search queries to exclude emails/phones

### T16 — Duplicate code (102)
- Files: `frontend/pages/positions/steps.ts` vs `frontend/composables/api-steps.ts`
- Fix: Consolidate to single `fetchWithRetry` with shared defaults

### T17 — Token in logs (96)
- File: `api/app/Services/JobSource/JobVision/JobVisionCrawler.php:242`
- Fix: Mask Authorization header in log output

### T8 — Admin page shape (76)
- File: `frontend/pages/admin/credentials/index.vue:168`
- Fix: Use `data?.data?.length` instead of `data?.length` for Laravel paginated response

### T2 — SQLi + IDOR (67.5)
- File: `api/app/Http/Controllers/Api/JobSourceCredentialController.php:72-77,90-94,105-152`
- Fix: Use parameterized queries, enforce ownership checks

### T12 — MIME verification (64)
- File: `api/app/Http/Controllers/Api/CandidateController`
- Fix: Add MIME type validation to file upload

### T14 — Contract drift (28)
- File: `contracts/jobsource.yaml`
- Fix: Sync YAML paths with actual `api/routes/api.php` routes

### T15 — E2E + CI/CD (23.3)
- Files: `e2e/`, `contracts/`, no `.github/workflows/`
- Fix: Update E2E endpoints, add CI pipeline
