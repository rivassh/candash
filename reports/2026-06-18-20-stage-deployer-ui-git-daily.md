# گزارش Git روزانه — stage-deployer UI chat

**Repo:** `ssh://192.168.160.30:2222/infra/stage-deployer.git`  
**مسیر محلی:** `/opt/stage-deployer`  
**بازه:** 2026-06-18 — 2026-06-20  
**چت:** `00632fa9-0281-4b7b-8760-1ec759f2be1b`

---

## خلاصه

| معیار | مقدار |
|--------|--------|
| commit در بازه (logged) | 4+ مرتبط مستقیم با UI chat |
| push به origin/main | **ناموفق** (protected branch) |
| ahead of origin | **14+** commit |
| working tree | **dirty** — فایل‌های modified/untracked زیاد |

---

## Timeline — commits مرتبط با این چت

### 2026-06-18

| Hash | Subject |
|------|---------|
| `3879bea` | Add project hook accordion, deploy pipeline view, and hash routing |
| `5fd524d` | Fix deploy pipeline accuracy for System Hook and deploy history |

**فایل‌های کلیدی:**

- `static/app.js` — hash router, pipeline, accordion, webhooks project page
- `static/app.css` — pipeline + accordion styles
- `static/index.html` — webhooks table columns, detail pipeline
- `scripts/webhook_request_log.py` — `slug`, `deploy_related`, `_match_slug`
- `scripts/hooks_server.py` — `normalize_gitlab_event`, API params

### 2026-06-19 (همان repo، احتمال session/jobs دیگر)

| Hash | Subject |
|------|---------|
| `92c0391` | Add local stage auto-heal cron and safer deploy health probes |
| `54e84d0` | Fix stage UI deploy history and sticky sidebar |

---

## Deploy / runtime

- UI از container `stage_deployer` با volume `./static` و `./scripts` serve می‌شود.
- verify: `curl -fsSk https://stage.artandev.ir/stage/` → **200**
- restart: `docker compose restart stage-deployer` در `/opt/stage-deployer`

---

## کارهای Git باقی‌مانده

1. MR برای ۱۴ commit ahead → `main`
2. commit یا stash تغییرات uncommitted (config/projects/*، scripts جدید، …)
3. tag/release در صورت نیاز (آخرین release note: `3cab41f` Release 0.7.0)

---

## diff stat (uncommitted — snapshot 2026-06-20)

```
M  static/app.js, app.css, index.html
M  scripts/hooks_server.py, github_webhook.py, project_registry.py, ...
M  config/projects/*.yaml (many)
?? scripts/deploy_routing.py, project_archive.py, stage-infra-watchdog.sh
?? docs/opt-handoff/, nextcloud/
```

---

## cross-ref

- [خلاصه چت](../chats/2026-06-20-stage-deployer-ui-cursor-session.md)
- [TODO](../cursor/todos/stage-deployer-ui-chat-open.md)
- [Cursor cost](2026-06-20-stage-deployer-ui-cursor-cost.md)
