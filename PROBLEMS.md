# TalentMatch — Known Problems

Prioritized issues from code review.

## 🚨 HIGH PRIORITY

### 1. Laravel Version Mismatch
- **`api/composer.json`**: requires `^13.0` but README documents Laravel 11
- Root cause: Inconsistent framework version causes deployment failures
- Fix: Align composer.json version with actual installed version or update documentation

### 2. Broken Test Suite
- No `phpunit.xml` configuration found
- Tests not runnable with `make test`
- composer.json requires Laravel 13 which has different test setup

### 3. Missing Database Configuration
- `.env` has production URL `https://candash.adlr.ir` for local development
- DB_PORT inconsistency: 5432 (env) vs 5433 (env.example) vs 5434 (.env)
- No proper database setup scripts

## ⚠️ MEDIUM PRIORITY

### 4. Port Conflicts & Environment Confusion
- **API_PORT**: 8080
- **FRONTEND_PORT**: 3000
- **MOCK_PORT**: 4000 (mock-jobsource), but e2e mock-server.ts uses 8086
- Conflicting `.env` files: `.env.example` vs `.env`

### 5. Frontend-Backend Coordination
- `frontend/composables/useApi.ts` uses `config.public.apiBase`
- `frontend/nuxt.config.ts` shows `/api` proxy config
- Multiple mock servers with overlapping routes

### 6. Missing Core Laravel Files
- `api/bootstrap/app.php` not found in review
- `api/config/app.php` not reviewed
- Route definitions in `api/routes/api.php` incomplete vs actual implementation

## ℹ️ LOW PRIORITY

### 7. Documentation Issues
- README.md has mixed Persian/English
- Makefile commands work but have complex compose commands
- No API development documentation

### 8. Code Structure Gaps
- Skill matcher uses simple ID resolution (acceptable for mock)
- Matching formula complete but lacks real-world complexity
- All services properly use interfaces (good architecture)

## Immediate Actions Required

1. **Fix Laravel version mismatch** — align composer.json with installed version
2. **Create phpunit.xml** — required for PHPUnit to work
3. **Standardize environment** — fix DB ports and remove production URLs from local .env
4. **Resolve port conflicts** — harmonize mock server and API ports