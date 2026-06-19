# گزارش روزانه Git — ۱۹ ژوئن ۲۰۲۶ (خرداد ۱۴۰۵)

**محیط:** `infra2` / `/opt`  
**تمرکز:** stage-deployer، پروژه‌های گروه مهدی، webhookها، UI پنل استیج  
**منبع:** چت Cursor + `git log` روی سرور

---

## خلاصه اجرایی

| حوزه | وضعیت |
|------|--------|
| نسخه پایین UI (گروه مهدی) | ✅ push + deploy استیج |
| پایداری استیج (auto-heal) | ✅ اسکریپت + cron (commit محلی) |
| تاریخچه deploy در UI | ✅ fix + backfill |
| مودال شاخه/تگ per-project | ✅ پیاده (commit محلی) |
| دکمه reload سرویس | ✅ پیاده (commit محلی) |
| GitHub webhook secret | ⚠️ نیاز به هم‌خوانی `GITHUB_WEBHOOK_SECRET` |
| push `stage-deployer` به GitLab `main` | ⚠️ branch protection (commitهای محلی) |

---

## پرامپت‌های روز (۲۸ پیام کاربر)

### راه‌اندازی و onboarding
1. `modular-gps-tracking-platform` — اولین deploy استیج؛ چه کم است؟ بدون پرامپت ممکن است؟
2. پروژه جدید از داشبورد — آیا `.env.stage` دستی لازم است؟
3. همه‌چیز خودکار توسط stage-deployer
4. compose حتماً در `infrastructure`؟
5. راهنما در UI stage-deployer

### نسخه UI (گروه مهدی)
6. همه پروژه‌های گروه مهدی نسخه پایین صفحه دارند؟
7. تأکید: باید نسخه در UI باشد
8. بررسی کامیت‌های قبلی / برنچ‌های دیگر (نگران حذف توسط AI)
9–10. «بگو» — گزارش وضعیت
11. «انجام بده» — پیاده‌سازی
12. push و deploy

### پایداری و UI پنل
13. چرا استیج پایین است؟
14. جلوگیری از تکرار / auto-heal
15. «برو» — اجرای auto-heal
16. مبهم بودن UI: «دیپلوی: هرگز»، webhook سبز/قرمز، sidebar sticky

### Webhook و routing
17. GitHub سبز ولی `invalid signature` در پنل
18. GitLab: `branch 'master' excluded`
19. مودال per-project: شاخه GitHub/GitLab + تگ production
20. `STAGE_TAG` هنوز معتبر است؟
21. توضیح HMAC signature
22. اشتباه: secret گیتلب برای گیتهاب
23. دکمه restart stage-deployer برای لود `.env`

### بستن چت و آرشیو
24. یادگیری‌ها + خلاصه پرامپت → `llfs`
25. تأیید کلید GitHub

---

## تاریخچه Git — `stage-deployer` (remote `main`)

```
54e84d0 Fix stage UI deploy history and sticky sidebar.
92c0391 Add local stage auto-heal cron and safer deploy health probes.
5fd524d Fix deploy pipeline accuracy for System Hook and deploy history.
3879bea Add project hook accordion, deploy pipeline view, and hash routing.
1a7fe82 fix(docker): mount VERSION and CHANGELOG into deployer container
3cab41f Release 0.7.0: version API, GitLab command log, deploy and UI updates.
```

### تغییرات محلی (همان روز، هنوز push نشده به `main`)

| فایل/ماژول | تغییر |
|------------|--------|
| `scripts/deploy_routing.py` | routing per-project (شاخه/تگ) |
| `scripts/deployer_restart.py` | restart کانتینر از UI |
| `scripts/hooks_server.py` | API deploy-routing + restart + webhook per-project |
| `scripts/github_webhook.py` | امضا + routing پروژه |
| `scripts/project_registry.py` | ذخیره فیلدهای routing در yaml |
| `static/app.js`, `index.html`, `app.css` | مودال 🔀 + دکمه ↻ reload |
| `.env.example` | `DEPLOYER_CONTAINER_NAME` |

---

## تاریخچه Git — پروژه‌های گروه مهدی (push شده)

| پروژه | commit | توضیح |
|--------|--------|--------|
| **erp-guarantie** | `2652976` | version footer + changelog روی main |
| **artexx** | `fa8416c` | basePath `/artexx` + version badge |
| **callcenter** | `646d182` | version badge + changelog |
| **chatbot-website** | `5640a85` | version badge + changelog |
| **sanaradyab** | `2f4103a` | version badge + changelog |
| **mix-proj** | `07daf9e` | مستندات deploy rule (نسخه از قبل v2.6.0) |

**استیج verify:** `https://stage.artandev.ir/{slug}/`

---

## `modular-gps-tracking-platform`

```
82ade52 feat(brand): لوگوی جدید — پورتال، وب و اندروید (0.8.14)
0362776 chore(submodules): bump portal, infrastructure, ui_references
b9a989a Add Cursor rule: deploy stage after task completion
```

کار روز: آماده‌سازی مسیر استیج → production (سؤال onboarding در ابتدای چت).

---

## تصمیم‌ها و یادگیری‌های فنی

1. **دو webhook، دو secret** — GitHub (HMAC) ≠ GitLab (token)
2. **HTTP 200 ≠ موفقیت** — `accepted: false` در body
3. **مسیر Stage اصلی = شاخه** — `STAGE_TAG` پیش‌فرض سراسری ماند
4. **`.env` فقط با restart کانتینر** — دکمه reload اضافه شد
5. **deploy-history** — CLI هم باید ثبت کند

---

## اقدامات باز

- [ ] push commitهای محلی `stage-deployer` وقتی branch protection باز شود
- [ ] هم‌خوان کردن `GITHUB_WEBHOOK_SECRET` با Secret در GitHub repo
- [ ] تنظیم مودال 🔀 برای پروژه‌هایی که push به `main`/`master` روی GitLab لازم دارند

---

## meta

- گزارش تهیه‌شده: 2026-06-19
- transcript: `ba6372af-50b5-46f4-b248-9a3513942f94`
- مخزن آرشیو: `git@github.com:rivassh/llfs.git`
