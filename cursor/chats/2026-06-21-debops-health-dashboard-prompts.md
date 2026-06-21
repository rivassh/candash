# Prompt summary � debops health dashboard

## Timeline of prompts

### 2026-06-16

- Add `/opt/new/debops/mikrotik/runbooks/INTERNAL-MONITORING.md` to the Health page.
- Add default credentials next to the listed monitoring systems in the internal monitoring runbook.

### 2026-06-20

- Discuss how to protect `http://172.16.1.164:8090/health.html` with a password and then open Zabbix/Metabase/etc without repeated login.
- Note that Nextcloud would have been a good portal if its login/AD integration were working, but it is still blocked.
- Proposed path: quick `nginx basic auth` for Health, then proper SSO with an IdP/reverse-proxy such as `authentik`; avoid storing/injecting service passwords into browser links.
- Improve the Health page visual style using glassmorphism.

### 2026-06-21

- Commit and push the Health dashboard change.
- Created focused commit `d611b81 Polish health dashboard glass styling`.
- Push was rejected because local `main` is ahead 1 and behind 2; rebase/pull was intentionally skipped due to a dirty working tree with unrelated changes.
- Run `llfs close TOPIC`; recorded this closeout as `debops-health-dashboard`.

## Technical decisions

- Keep the Health UI as a static portal served by monitoring Nginx.
- Serve the Markdown runbook from the same portal path (`/INTERNAL-MONITORING.md`).
- Preserve production secrets outside git; only record default/initial credentials in the runbook.
- Treat SSO as a separate implementation track from the visual Health page polish.
