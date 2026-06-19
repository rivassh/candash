# TODO — stage-deployer chat (open items)

**Opened:** 2026-06-07 (closeout)  
**Transcript:** `1f3fb1df-2297-45af-978c-92fc489fd128`  
**Workspace:** `/opt` (infra2 / stage-deployer)

## GitLab CI & pipeline سبز

- [ ] به‌روزرسانی `STAGE_BASE_DOMAIN` از `stage.sana.adlr.ir` → `stage.artandev.ir` در همه `.gitlab-ci.yml`
- [ ] تمایز verify **path-based** (`/mix-proj/`) vs **ref-based** (`{ref}.stage.artandev.ir`)
- [ ] افزودن `templates/gitlab-ci.path-stage.yml` در stage-deployer + propagate
- [ ] `.gitlab-ci.yml` برای `mix-proj` و repoهای بدون CI (erp-guarantie, cbrender, …)

## Webhook & deploy خودکار

- [ ] GitLab: **Project Webhook** روی هر repo (نه System Hook)
- [ ] `hooks_server.py`: پذیرش System Hook با `object_kind: push`
- [ ] deploy `main` برای پروژه‌های path-based (یا مستندسازی صریح که فقط agent/`deploy-project.sh`)
- [ ] UI: auto-poll webhook log + correlate با `deploy-history.json`
- [ ] GitHub webhook: اطمینان از secret هم‌خوان + URL `/webhook/github` (نه `/webhook/gitlab`)

## stage-deployer repo

- [ ] push commit `95cc36c` و بعدی‌ها — `main` protected → MR یا unprotect موقت
- [ ] README: هم‌خوان با مدل path + `stage.artandev.ir` (README هنوز ساب‌دامین قدیمی دارد)

## Nextcloud / AD

- [ ] LDAP re-enable با LDAPS/636 وقتی AD (`172.16.1.246:389`) پایدار شد
- [ ] `ad-nextcloud-bind` — bind account + `occ ldap:test-config`
- [ ] راهنمای LDAP client روی PC لینوکس (جدا از Nextcloud server-side)

## میکروتیک / monitoring / bandwidth

- [ ] flag/log per-service در میکروتیک برای تحلیل مصرف
- [ ] export metrics به Zabbix/Grafana/Prometheus/ELK
- [ ] سند راهکارهای کاهش پهنای باند (CDN bypass، cache، split traffic)

## امنیت

- [ ] چرخش `GITLAB_WEBHOOK_SECRET` / `GITHUB_WEBHOOK_SECRET` اگر در چت لو رفته
- [ ] چرخش Cursor session — cookie در closeout paste شده (commit نشده)

## انجام‌شده در این چت (مرجع)

- [x] webhook request logging
- [x] Nextcloud stack + org dashboard integration
- [x] migrate domain → `stage.artandev.ir`
- [x] GitHub webhook async deploy path
- [x] stage-deployer روی docker compose (نه systemd)
- [x] fix Nextcloud login (LDAP off + admin reset)
- [x] fix server timezone → Asia/Tehran
- [x] Cursor rule deploy-after-changes → propagate به ~۱۵ پروژه
- [x] مستند روال stage-deployer برای تحلیل AI
