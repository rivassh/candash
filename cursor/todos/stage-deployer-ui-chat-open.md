# TODO — باقی‌مانده از چت stage-deployer UI

**چت:** `00632fa9-0281-4b7b-8760-1ec759f2be1b`  
**آخرین به‌روزرسانی:** 2026-06-20  
**وضعیت:** باز — قبل از بستن چت

---

## اولویت بالا

- [ ] **Push به GitLab `main`:** شاخه `main` در `infra/stage-deployer` protected است؛ ۱۴+ commit محلی push نشده → MR یا دسترسی Maintainer لازم است.
- [ ] **تغییرات uncommitted:** ده‌ها فایل modified/untracked در `/opt/stage-deployer` (config/projects، hooks_server، static، scripts جدید) — commit جدا یا discard عمدی.
- [ ] **چرخش session Cursor:** توکن `WorkosCursorSessionToken` در پرامپت #۴ لو رفت → logout/login یا revoke session.

## UI / QA

- [ ] **E2E hash routing:** تست دستی `#/projects?expand=slug`، `#/webhooks/project/slug`، `#/webhooks?detail=id`، `#/logs?project=slug` بعد از refresh.
- [ ] **Pipeline سبز واقعی:** push به شاخه deploy-eligible (نه `main`) بعد از fix System Hook — تأیید outcome=`deployed` در لاگ.
- [ ] **پروژه بدون webhook deploy:** نمایش fallback از `last_deploy` در آکاردئون را روی ۲–۳ slug تست کن.

## زیرساخت / ops

- [ ] **GitLab webhook:** System Hook کافی است اگر `normalize_gitlab_event` فعال باشد؛ در صورت نیاز webhook پروژه‌ای Push/Tag Push هم اضافه شود.
- [ ] **لاگ تاریخی:** رکوردهای قدیمی `System Hook` + `ignored` در JSON retroactive سبز نمی‌شوند (فقط webhookهای جدید).
- [ ] **`STAGE_REF_EXCLUDE`:** push به `main`/`master`/`develop` عمداً deploy نمی‌شود — اگر Stage روی main است، per-project override در UI.

## مستندات / repo

- [ ] **`docs/opt-handoff/`** و **`nextcloud/`** untracked — تصمیم commit یا gitignore.
- [ ] **اسکریپت‌های جدید:** `deploy_routing.py`, `project_archive.py`, `stage-infra-watchdog.sh` — review و commit در batch جدا.

---

## انجام‌شده در این چت ✓

- [x] آکاردئون پروژه + آخرین webhook deploy-related
- [x] دکمه ⚡ + صفحه `#/webhooks/project/{slug}`
- [x] Pipeline شبیه GitLab (Webhook → Token → پردازش → Deploy)
- [x] Hash router + history (refresh state حفظ می‌شود)
- [x] فیلتر API `slug` + `deploy_related`
- [x] `normalize_gitlab_event` برای System Hook
- [x] Enrich pipeline از `last_deploy`
- [x] Restart container + verify `https://stage.artandev.ir/stage/` (200)
