# TODO — باقی‌مانده از چت Production / UI / Webhook / Nextcloud

**چت:** `a07970af-450e-48d5-8951-3c1007dc6453`  
**آخرین به‌روزرسانی:** 2026-06-20  
**وضعیت:** باز (closeout — عمداً نگه داشته شد)

---

## اولویت بالا — stage-deployer

- [ ] **push `stage-deployer`:** `main` حدود **۱۵ commit** جلوتر از `origin` — push به GitLab (`prompts/stage-deployer` + در صورت نیاز `infra/stage-deployer`)
- [ ] **webhook secrets:** GitLab Hook #1597 و GitHub → `403 invalid_token` / `invalid signature` — هم‌خوان کردن `GITLAB_WEBHOOK_SECRET` و `GITHUB_WEBHOOK_SECRET` با پنل GitLab/GitHub
- [ ] **SMS آلارم:** در `.env` stage-deployer (دست کاربر): `WEBHOOK_ALERT_SMS=1`, `WEBHOOK_ALERT_SMS_MOBILE`, `ALERT_SMS_HTTP_URL` — تست یک webhook عمدی
- [ ] **Mattermost خاموش بماند:** `WEBHOOK_ALERT_MATTERMOST=0` (یا unset)
- [ ] **Nextcloud submodule remote:** ایجاد `infra/nextcloud.git` + `git push` از `/opt/stage-deployer/nextcloud` + commit gitlink در والد

## Production per-project

- [ ] **`erp-guarantie`:** `prod_url` ست شده — در صورت deploy واقعی SSH: `prod_host`, `prod_path` در UI یا yaml
- [ ] **`mix-proj`:** `prod_url` → `https://crm.sana-gps.ir` — همانند بالا برای SSH
- [ ] **تست release\***: یک تگ `release-test-*` روی پروژهٔ آزمایشی → `deploy-prod-safe.sh` بدون شکست CI

## زیرساخت / سلامت

- [ ] **AD → Nextcloud LDAP:** `ldap:test-config` fail (`LDAP server is shutting down`) — شبکه/DC از داخل container
- [ ] **`sanaradyab` down** (از health قبلی) — scylla/502 جداگانه
- [ ] **چرخش session Cursor** — cookie دوباره در پرامپت closeout (۲۰۲۶-۰۶-۲۰) paste شد

## از چت‌های قبلی (هنوز باز — merge در TODO.md)

- [ ] modular-gps build fail + deploy
- [ ] sanaradyab-ws 502
- [ ] debops / passbolt / network follow-ups — see [TODO.md](../TODO.md)

---

## انجام‌شده ✓ (این چت)

- [x] تگ `release*` → production (GitLab + GitHub mirror)
- [x] API/UI تنظیم Production per-project (`PUT …/production`, مودال در grid)
- [x] SMS alert (`alert_notify.py`) — Mattermost opt-in
- [x] Webhook همیشه HTTP 200 برای GitLab/GitHub (CI سبز)
- [x] UI hub یکپارچه (پروژه + Stage + bulk actions + responsive)
- [x] منوی کناری collapsible + localStorage
- [x] `prod_url`: erp-guarantie → gurarantiee.sana-gps.ir, mix-proj → crm.sana-gps.ir
- [x] health live/fix (`get_all_last` import، subprocess health)
- [x] Nextcloud → submodule `stage-deployer/nextcloud` + symlink `/opt/nextcloud`
- [x] راهنمای Cursor Cloud + GitHub (توضیح در چت)
