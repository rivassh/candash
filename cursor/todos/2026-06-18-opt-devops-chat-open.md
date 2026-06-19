# TODO — باقی‌مانده از چت `/opt` + devops + stage-deployer

**چت:** `b8973106-6874-49f9-8474-e93f694cb515`  
**آخرین به‌روزرسانی:** 2026-06-20  
**وضعیت:** باز

---

## اولویت بالا

- [ ] **MR به `main`:** `release/0.7.0` روی `infra/stage-deployer` push شده؛ merge به `main` protected هنوز نشده.
- [ ] **`git checkout main && git pull`** روی infra2 بعد از merge MR.
- [ ] **چرخش session Cursor:** cookie/session در پرامپت closeout لو رفت → logout/login Dashboard.

## repo / GitLab

- [ ] **`nextcloud/` submodule:** untracked در stage-deployer — commit + push یا حذف عمدی.
- [ ] **`docs/opt-handoff/`:** untracked — commit جدا یا نگه‌داری خارج git.
- [ ] **Remote قدیمی:** هر clone با `prompts/stage-deployer` → `infra/stage-deployer` به‌روز شود.
- [ ] **devops:** cursor-prompt-metrics روی `main` merge شد ✓ — submodule pointer در stage-deployer بعد از MR هم‌راستا شود.

## prompt-metrics / Cursor

- [ ] **همبستگی دقیق prompt ↔ billing:** export CSV شناسهٔ چت ندارد؛ فقط تخمین زمانی — اگر API بهتر آمد، اسکریپت merge بنویس.
- [ ] **(اختیاری) tiktoken:** نصب `--user tiktoken` برای تخمین دقیق‌تر محلی.
- [ ] **Hook تست:** بعد از reload Cursor، یک prompt بزن و `devops/cursor-prompt-metrics/data/hook.log` را چک کن.

## ops

- [ ] **Docker route به GitLab:** اگر دوباره `No route to host` شد، bridge `192.168.160.0/20` (erp-guarantie) vs IP واقعی GitLab را چک کن — VPN لازم نبود.
- [ ] **tag `v0.7.0`:** روی `infra/stage-deployer` هست؛ بعد از merge MR تأیید کن روی `main` هم درست است.

---

## انجام‌شده ✓

- [x] انتقال prompt-metrics به `devops` submodule
- [x] DevOps Health در منوی Stage + API
- [x] تمیزکاری loose files در `/opt`
- [x] `VERSION` 0.7.0 + `CHANGELOG.md` + `docs/GITLAB-COMMANDS.md`
- [x] push `devops` → `infra/devops.git` main
- [x] push `release/0.7.0` + tag `v0.7.0`
- [x] mount `VERSION` در container deployer
