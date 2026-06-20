# گزارش روزانه Git + فعالیت — modular-gps-tracking-platform

**مخزن:** `modular-gps-tracking-platform` (origin: GitLab `192.168.160.30:2222`)  
**بازه:** ۱۹–۲۰ ژوئن ۲۰۲۶ (≈ ۲۹–۳۰ خرداد ۱۴۰۵)  
**منبع:** `git log` + چت Cursor `6e006fe0-d241-4770-8a6e-5022856a79b2` + submodule `infrastructure`

---

## خلاصه commitهای Git (push شده — main repo)

| تاریخ | SHA | پیام commit | حجم / یادداشت |
|--------|-----|-------------|----------------|
| 2026-06-19 | `8653850` | chore(cursor): add per-prompt token budget rule | rule |
| 2026-06-19 | `3220408` | qa list | docs |
| 2026-06-19 | `e5800ec` | Merge branch 'master' | sync |
| 2026-06-19 | `0362776` | chore(submodules): bump portal, infrastructure, ui_references | submodules |
| 2026-06-19 | `82ade52` | feat(brand): لوگوی جدید سناردیاب (0.8.14) | web/brand |
| 2026-06-19 | `709f3ef` | Bump portal-sana-gps: target=_blank | portal submodule |
| 2026-06-19 | `0671035` | Document legacy decommission, Arvan SSL, monorepo eval | docs |
| 2026-06-19 | `5f84eca` | Add Cursor usage tracking workflow | ai/cursor-usage |
| 2026-06-19 | `7dfe7a7` | traccar env | config |
| 2026-06-20 | `b893ff8` | fix(registration): cross-type IMEI/serial match via SMS catalog | **0.8.18** |
| 2026-06-20 | `5407af5` | feat(db): Phase 1 unified tree and decoupled RBAC migrations | feature branch |
| 2026-06-20 | `c6f5da9` | feat(enterprise): Phase 1 steps 3–4 — users→nodes + TS tree scope | feature branch |
| 2026-06-20 | `dcfc389` | Merge feature/enterprise-phase1-nodes-rbac | merge |
| 2026-06-20 | `51ae98f` | fix(web): hourly position check storage in Vitest node env | test fix |
| 2026-06-20 | `5c2f65d` | fix(common): narrow anchor null check in route stop detection | tsc |
| 2026-06-20 | `4da40c0` | fix(device-service): type route playback query as RawRouteRow | tsc — **HEAD** |

---

## Submodule `infrastructure` (postgres migrations)

| تاریخ | SHA | پیام |
|--------|-----|------|
| 2026-06-20 | `943a5db` | feat(db): schema gap repair and Phase 1 unified tree RBAC |
| 2026-06-20 | `bb29b9a` | fix(db): repair migration 70 syntax and users→nodes data migration |

**Migrationهای جدید اعمال‌شده روی prod:** 70, 71, 72, 73, 74, 75 (۶ فایل)

---

## ۱۹ ژوئن — ۲۹ خرداد

### Git
۹ commit — عمدتاً docs، brand v0.8.14، submodule bumps، Cursor usage workflow.

### فعالیت (Cursor / ops)

| موضوع | توضیح |
|--------|--------|
| Brand refresh | لوگوی سناردیاب در portal، web، android |
| Legacy docs | decommission + Arvan SSL + monorepo evaluation |
| Cursor hygiene | token budget rule، usage tracking |
| Traccar env | تنظیم env مرتبط |

---

## ۲۰ ژوئن — ۳۰ خرداد

### Git
۷ commit feature + merge + ۳ fix build/test — **session اصلی enterprise + IMEI**.

### فعالیت فنی

| وضعیت | موضوع | جزئیات |
|--------|--------|--------|
| ✅ | IMEI/SMS cross-type | `evaluateDeviceCheckInbound` + UI handshake — changelog 0.8.18 |
| ✅ | Migration repair 31 | `device_models.*`, `devices.deleted_at` قبل از replay |
| ✅ | Schema 74 | `nodes`, `permission_groups`, `dynamic_roles`, `node_tags`, … |
| ✅ | Fix migration 70 | `sales_rep`, `role_permissions` INSERT کامل |
| ✅ | Data migration 75 | users → nodes + role assignments |
| ✅ | TS nodes scope | `packages/common/src/nodes.ts`, `auth.getVisibleUserIds` |
| ✅ | Remote test | 845/845 Vitest (`REMOTE_TEST_DOCKER=1`) |
| ✅ | Prod deploy backend | ۶ سرویس + migrate |
| ✅ | APK | sana-gps-latest.apk |
| ⚠️ | Prod web | `WEB_PORT=8081` conflict با metabase-adminer — deploy web pending |
| ❌ | E2E hierarchy | `09-novin-hierarchy-journey.cy.ts` — OTP/register-password |
| 📝 | WIP unstaged | stash `wip-unstaged-pre-push` — registration-approval, RBAC admin, E2E |

---

## آمار تقریبی

| معیار | مقدار |
|--------|--------|
| Commits main (۱۹–۲۰) | 16 |
| Commits infrastructure | 2 |
| نسخه changelog منتشر | 0.8.18 |
| تست remote | 845 pass |
| Migration prod جدید | 6 |

---

## کار باز برای ۲۱ ژوئن+

1. E2E hierarchy + registration OTP env  
2. Enterprise Phase 1 cutover (`permissions.ts`)  
3. Triage stash / unstaged WIP  
4. Prod web deploy (پورت)  
5. لوکال: `make migrate` برای verify 70–75  

---

## مراجع

- Session chat: `chats/2026-06-20-modular-gps-cursor-session.md`
- BACKLOG: `ai/ideas/BACKLOG.md` — بخش session 2026-06-20
- Todo ops: `docs/todo-list-1.md` — بخش G
