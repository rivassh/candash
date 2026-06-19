# TODO — باقی‌مانده از چت سلامت/دیپلوی stage

**چت:** `a93bbde8-393a-443d-a2a2-379651d03f35`  
**آخرین به‌روزرسانی:** 2026-06-19  
**وضعیت:** باز (عمداً به تعویق افتاده — closeout)

---

## اولویت بالا

- [ ] **دیپلوی `modular-gps`:** build شکست (`npm run build -w @gps/integration-registry`) — رفع + `deploy-project.sh modular-gps master`
- [ ] **commit/push `stage-deployer`:** ۶ commit جلوتر از origin + working tree بزرگ (۳۵+ فایل) — جدا کردن موضوعی قبل از push
- [ ] **502 `/sanaradyab-ws/`:** کانتینر `stage_sanaradyab_ws` / پورت 18059 / احتمال crash kafka consumer
- [ ] **verify نهایی ۶ پروژه:** sanaradyab, modular-gps, mix-proj, cbrender, render-engine, voip-tts — `system_health.py --keep ...`

## مستندات / اتوماسیون

- [ ] **تاریخچه سوالات AI:** سیستم نگهداری prompt history (بعداً — در TODO مرکزی)
- [ ] **NEW-PROJECT-GUIDE:** بخش merge `docker-compose.stage.yml` + API/WS paths + push stage-deployer
- [ ] **cron health:** `system_health.py` روی keep-list + alert Mattermost
- [ ] **Nexus داخلی:** `daemon.json` + `env-docker-build.sh` + الگوی traccar-mvp (راهنما در subagent Nexus آماده است)

## GitLab / webhook

- [ ] **`.gitlab-ci.yml` ساده:** پروژه‌ای که با runner/k8s پر شده — برگشت به `templates/gitlab-ci.stage.yml`
- [ ] **webhook secrets:** ۱۹ رویداد `invalid_token` / `invalid signature` — هم‌خوان کردن GitLab + GitHub با `.env`

## امنیت

- [ ] **چرخش session Cursor:** cookie در پرامپت closeout لو رفت — logout/login Dashboard

---

## انجام‌شده ✓

- [x] گزارش سلامت ۳۹ پروژه (API + HTTP probe)
- [x] `sanaradyab` frontend: `docker start stage_sanaradyab_frontend` → HTTP OK (13/13 stack)
- [x] `mix-proj`: `docker start stage_mix_proj_app`
- [x] fix merge compose در `deploy-project.sh` (commit محلی `1212f1c` — push نشده)
- [x] دیپلوی موفق: `cbrender`, `voip-tts`, `render-engine`
- [x] راهنمای Nexus (subagent) — بدون پیاده‌سازی

## دیپلوی پس‌زمینه — وضعیت

| کار | وضعیت |
|-----|--------|
| `cbrender` deploy (shell) | ✓ تمام |
| `voip-tts` deploy | ✓ تمام |
| `render-engine` deploy | ✓ تمام |
| subagent [ادامه دیپلوی](b95f34c0-1c2d-4e87-9c62-04a83863fd5a) | ✗ قطع workspace — تمام نشد |
| دیپلوی فعال در پس‌زمینه | **خیر** (در لحظه closeout)
