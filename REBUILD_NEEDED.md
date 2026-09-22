# API Container Rebuild Required

## Problem
`candash-api-1` is `unhealthy` — migration step in CMD chain fails with `SQLSTATE[42P07]: Duplicate table "job_source_credentials" already exists`.

## Root Cause
Production DB already contains `job_source_credentials` table. The 2026 migration `2026_09_19_000001_create_job_source_credentials_table.php` runs `Schema::create()` which fails with 42P07 error when table exists, before `php artisan serve` starts.

## Fix Applied
Both migrations made idempotent with `Schema::hasTable()` guards:

1. **api/database/migrations/2026_09_19_000001_create_job_source_credentials_table.php**
   ```php
   if (Schema::hasTable('job_source_credentials')) {
       return;
   }
   ```

2. **api/database/migrations/2024_01_02_000001_create_job_source_credentials_table.php**
   ```php
   if (!Schema::hasTable('job_source_credentials')) {
       Schema::create('job_source_credentials', ...);
   }
   ```

## Outstanding Action
Rebuild the api container and restart:

```bash
docker compose build api
docker compose up -d api
```

Then verify:
```bash
curl -sf http://127.0.0.1:8082/api/health
docker logs candash-api-1 | grep -E "INFO Server running|migrate"
```

## Production DB Rules
- Only `php artisan migrate --force` (idempotent) and `db:seed --force` allowed
- NO migrate:fresh, rollback, db:wipe, TRUNCATE, DROP TABLE, or direct SQL writes

## Status
- Migration fixes: ✅ In source
- Container rebuild: ❌ Not yet (old image still running, created 2026-09-19T15:38:58)
- Health check: ❌ FailingStreak ~743