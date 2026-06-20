# خلاصه چت Cursor — workspace `modular-gps-tracking-platform`

**تاریخ:** ۱۹–۲۰ ژوئن ۲۰۲۶ (≈ ۲۹–۳۰ خرداد ۱۴۰۵)  
**مخزن:** `modular-gps-tracking-platform` (لوکال: `/home/hamid/Desktop/...`)  
**شناسه چت (Cursor):** `6e006fe0-d241-4770-8a6e-5022856a79b2`

---

## خلاصهٔ پرامپت‌ها (به ترتیب)

| # | پرامپت کاربر | نتیجهٔ کلی |
|---|--------------|------------|
| 1 | IMEI و کد دستگاه mutually exclusive نیستند — تطبیق از داده سیستم + SMS | سیاست محصول تأیید شد |
| 2 | «شروع کن» — پیاده‌سازی cross-type SMS match | v0.8.18 — `b893ff8` |
| 3 | Enterprise Phase 1 Steps 1–2: migration repair + schema nodes/RBAC | infra `943a5db` — migrations 31, 74 |
| 4 | «گام‌های بعدی» — fix migration 70، Step 3 (75)، Step 4 (nodes.ts + auth) | infra `bb29b9a` + `c6f5da9` |
| 5 | خطای migration 70: `syntax error at or near "ON"` | INSERT کامل `role_permissions` + `sales_rep` |
| 6 | `commit push` — merge، push، remote test، prod deploy | merge `dcfc389` → `4da40c0`؛ 845/845 test؛ backend deploy OK |
| 7 | لوکال: `.env.production` لازم نیست | `.env` + `make develop-local` + `make migrate` |
| 8 | بستن چت: TODO، یادگیری، خلاصه پرامپت، push به llfs | این فایل + گزارش git |

---

## کارهای انجام‌شده

| موضوع | commit / محل |
|--------|----------------|
| IMEI ↔ serial cross-type SMS match | `b893ff8` — `device-check-inbound-eval.ts`, UI handshake |
| Migration gap repair (31) | infra `943a5db` — `31-schema-prerequisites-repair.sql` |
| Unified tree RBAC schema (74) | infra `943a5db` — `nodes`, `dynamic_roles`, … |
| Fix migration 70 syntax | infra `bb29b9a` |
| Users → nodes data migration (75) | infra `bb29b9a` |
| TS tree scope (`nodes.ts`, `auth.ts`) | `c6f5da9` |
| Merge feature branch | `dcfc389` |
| Build/test fixes (Vitest localStorage, tsc) | `51ae98f`, `5c2f65d`, `4da40c0` |
| Changelog | **0.8.18** — `apps/web/src/config/changelog.json` |
| Remote test | 845/845 pass (`REMOTE_TEST_DOCKER=1`) |
| Prod deploy backend | auth, admin, device, event-router, user, api-gateway + migrate 70–75 |
| APK build | `infrastructure/mobile/releases/sana-gps-latest.apk` (~8.2MB) |

---

## TODO باز (نگهداری در BACKLOG + `docs/todo-list-1.md` بخش G)

| ID | عنوان | اولویت |
|----|--------|--------|
| G1 | E2E `09-novin-hierarchy-journey.cy.ts` — گیر `register-password`/OTP | بالا |
| G2 | Enterprise Phase 1 cutover — `permissions.ts` ← `getNodePermissionCodes` | بالا |
| G3 | Enterprise Phase 2+ — Redis live، multi-tenant، Traccar parser-only | بالا |
| G4 | WIP unstaged / `stash@{0}: wip-unstaged-pre-push` — triage | متوسط |
| G5 | CL-004 Metabase WS monitoring | متوسط |
| G6 | لوکال migrate 70–75 — `make migrate` روی develop | پایین |

---

## چیزهایی که احتمالاً نمی‌دانستید (یا ارزش یادآوری دارد)

### ۱. IMEI و سریال دستگاه مکمل‌اند، نه جایگزین

مدل محصول: IMEI (اسلات SIM) و کد/سریال دستگاه **هر دو** می‌توانند وجود داشته باشند. منطق قدیمی either/or در UI/evaluator اشتباه بود. **بهترین منبع حقیقت:** پاسخ SMS check که ردیف کاتالوگ را از SIM برمی‌گرداند — cross-type match وقتی همان ردیف است.

### ۲. شماره‌گذاری migration با gap = replay شکسته

فایل `31` وجود نداشت ولی migrationهای بعدی (`33`, `42`, …) به ستون‌های `device_models.region` و `devices.deleted_at` نیاز داشتند. راه‌حل: migration **repair غیرمخرب** (`31-schema-prerequisites-repair.sql`) **قبل از** replay وابسته‌ها — نه `down -v` روی postgres.

### ۳. لوکال ≠ prod — env جدا

| محیط | env | compose |
|------|-----|---------|
| **لوکال dev** | `.env` | `infrastructure/docker-compose.yml` — `make develop-local`, `make migrate` |
| **prod سرور** | `.env.production` | `docker-compose.prod.yml` — `make prod-deploy-safe` |

روی دسکتاپ **نیازی به `.env.production` نیست** مگر عمداً prod-like تست کنید.

### ۴. `infrastructure` یک git submodule است

migrationها در submodule push می‌شوند؛ سپس pointer در main repo bump. فراموش کردن push submodule → prod migrate قدیمی می‌ماند.

### ۵. درخت سلسله‌مراتب: materialized path vs CTE

Phase 1: جدول `nodes` با `path` (materialized path) + `node_role_assignments`. TS: `getVisibleUserIds` وقتی tree ready است از nodes می‌خواند؛ وگرنه fallback به `parent_id` CTE. **cutover کامل هنوز نشده** — `users.role` هنوز authoritative است.

### ۶. Vitest در Node env localStorage ندارد

کد web که `localStorage` می‌خواند در تست unit شکست می‌خورد — نیاز به in-memory fallback (مثل `hourlyPositionCheck.ts`).

### ۷. build monorepo: `npm run build -w @gps/common` نه `npx tsc`

`npx tsc` ممکن است از registry داخلی (`nexus.lan`) با SSL fail بگیرد؛ از workspace script استفاده کنید.

### ۸. Docker prod build unstaged WIP را هم می‌بیند

اگر فایل unstaged در context build باشد، خطای tsc از WIP نیمه‌کاره می‌آید — قبل از deploy working tree را تمیز یا stash کنید.

### ۹. conflict پورت web prod (فقط برخی ماشین‌ها)

`WEB_PORT=8081` با `metabase-adminer` conflict — روی prod واقعی ممکن است نباشد؛ روی لوکال با compose adminer دیده شد. fix: `WEB_PORT` دیگر یا stop adminer.

### ۱۰. stage-deployer روی دسکتاپ hamid نیست

rule `stage-deploy-after-changes` فقط وقتی `/opt/stage-deployer` وجود دارد — روی این ماشین skip طبیعی است.

---

## وضعیت git پایان session

- **Branch:** `master` @ `4da40c0` — sync با `origin/master`
- **Stash:** `wip-unstaged-pre-push` + فایل‌های modified (BACKLOG, registration-approval WIP, E2E, …)
- **Submodule infra:** `bb29b9a`

---

## نکته برای چت‌های بعدی

- Enterprise: مشخص کنید **Phase 1 cutover** یا **Phase 2+ spec**
- E2E: env OTP/mock را قبل از hierarchy journey سبز کنید
- deploy: `SVC=` محدود — web جدا اگر فقط frontend عوض شده
- لوکال: همیشه `make develop-local` + `make migrate` — نه prod env
