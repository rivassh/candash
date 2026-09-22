Topic: candash API container (`candash-api-1`) is stuck unhealthy — `php artisan serve` never boots because a 2024 job-source-credentials migration fails on the shared prod DB (duplicate table `job_source_credentials`).

### Execution context
- User reported the API container is unhealthy after a rebuild.
- Healthcheck in the 2026 migration's CMD runs `curl -fsS ... | grep 'mysql|mysql2'`, but the container is `php:8.4-fpm-bookworm`/Postgres; its debug patch returns a falsy string, so health is `/bin/true` → "healthy". Credentials come from `/etc/hosts` + `conn.redis.host=redis` in the compose env.
- 2024 migration has no `Schema::hasTable` guard (not idempotent); the 2026 migration has the guard already.
- DB $DB_PASSWORD / etc. pin the table to the same 2024 schema the prod DB already holds.

### Live resources
- API container `candash-api-1`
- Docker compose project `candash` (services: api, meilisearch, postgres `candash-db-1`, redis)
- Local dev `docker compose up -d --build api`
- Prod DB with existing `job_source_credentials` table (do not drop/wipe)

### Session metadata
- Target repo: /opt/websites/candash
- Worst-case path to fix: make `database/database/migrations/1234_create_..._table.php` idempotent (guard with `Schema::hasTable`).
- `DELETE FROM job_source_credentials` is a write to prod; needs user approval (or make migration idempotent instead).

### Discovered
- Healthcheck `curl ... | grep 'mysql|mysql2'` fails → HEALTHCHECK always fails.
- 2024 migration must be made idempotent via `Schema::hasTable` guard (like `2026_09_19...`).
- Dockerfile CMD runs migrate at runtime; making 2024 migration idempotent aligns with the 2026 style.
- Drift summary: api image was rebuilt at 2024-01-02 due to composer duplicate error in the entrypoint.

### Dead ends
- Healthcheck intent unclear — 2026 migration says `mysql|mysql2`; container is Postgres; grep returns falsy → `/bin/true`.
- No confirm action requested for the DB yet — I need user approval before DELETE on prod.
