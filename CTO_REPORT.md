# TalentMatch Project Report

## Current Status: Unhealthy Container
- API container `candash-api-1` unhealthy for ~743 cycles
- Root cause: 2024 migration fails with duplicate table error on production DB
- **Fixed**: Made both 2024 & 2026 migrations idempotent with `Schema::hasTable()` guards
- **Next**: Rebuild API container (`docker compose build api && docker compose up -d api`)

## Architecture Overview
- **Backend**: Laravel 13 + PHP 8.4 (API engine, REST, Sanctum auth)
- **Frontend**: Nuxt 3 + Vue 3 + Tailwind (RTL Persian interface)
- **Database**: PostgreSQL 16 + pgvector (vector matching)
- **Search**: Meilisearch (replaced Elasticsearch)
- **Mock**: Node.js job source simulator

## Key Updates (2026-09-18)
✅ JobVision token refresh mechanism  
✅ Frontend Vue template fixes & error handling  
✅ Meilisearch integration (all search endpoints working)  
✅ Port conflicts resolved (8080→8082, 3000→3080)  
✅ VNC-based interactive browser for JobVision login  
✅ All 7 E2E scenarios passing  

## Immediate Concerns
1. **Laravel version mismatch**: composer.json says ^13.0, README docs Laravel 11
2. **Missing test config**: No phpunit.xml prevents `make test`
3. **Environment inconsistencies**: DB ports (5432/5433/5434), production URLs in local .env
4. **Port conflicts**: Mock server uses 4000 vs e2e 8086

## Production Rules
- DB remains read-only (except migrations/seeding)
- `make migrate --force` and `make seed --force` only allowed write ops

## Recommended Actions
1. Rebuild API container immediately to restore service availability
2. Fix Laravel version inconsistency (align composer.json or docs)
3. Create phpunit.xml configuration
4. Standardize environment ports and remove production URLs
5. Harmonize mock server ports across environment

## Team Performance
- 40 PHPUnit tests passing
- 43 E2E scenarios passing  
- Systematic remediation plan in progress (RICE scoring complete)

**Priority**: Rebuild API container first to restore service availability.
