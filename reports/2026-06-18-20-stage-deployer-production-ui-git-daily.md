# Daily git report — stage-deployer production / UI / webhook session

**Dates:** 2026-06-18 – 2026-06-20 (Asia/Tehran)  
**Transcript:** `a07970af-450e-48d5-8951-3c1007dc6453`  
**Focus:** production deploy، webhook/SMS، UI hub، health، Nextcloud submodule

## Summary

| Item | Status |
|---|---|
| Repo | `stage-deployer` @ `/opt/stage-deployer` |
| Commits (Jun 18–20, local) | 15 on `main` |
| vs `origin/main` | **15 ahead — not pushed** |
| Working tree | clean (at closeout) |
| Submodules | `devops`, `nextcloud` (gitlinks committed) |

## Commits (newest first)

```
4ee2101 2026-06-20 Add project archive system and bulk-archive stage projects
54e84d0 2026-06-19 Fix stage UI deploy history and sticky sidebar
92c0391 2026-06-19 Add local stage auto-heal cron and safer deploy health probes
5fd524d 2026-06-18 Fix deploy pipeline accuracy for System Hook and deploy history
3879bea 2026-06-18 Add project hook accordion, deploy pipeline view, and hash routing
1a7fe82 2026-06-18 fix(docker): mount VERSION and CHANGELOG into deployer container
1613cf7 2026-06-18 docs: update GITLAB-COMMANDS after successful push to infra remotes
b646663 2026-06-18 chore: bump devops submodule after rebase onto remote main
3cab41f 2026-06-18 Release 0.7.0: version API, GitLab command log, deploy and UI updates
fcac52d 2026-06-18 fix(deploy): keep bootstrap logs off stdout, detect standalone stage compose
70373a8 2026-06-18 fix(modular-gps): use standalone stage compose file on deploy
1212f1c 2026-06-18 fix(deploy): merge base compose with stage override files
95cc36c 2026-06-18 Add Cursor rule: deploy stage after task completion
20e3d8d 2026-06-18 Add devops submodule with prompt metrics and DevOps Health in stage UI
```

## Feature ↔ commit mapping (this chat)

| Feature | Where |
|---|---|
| `alert_notify.py`, webhook HTTP 200, SMS | `3cab41f` + follow-ups in `5fd524d` |
| Production API / GitHub release prod | `3cab41f`, `deploy-prod-safe.sh` |
| UI hub grid, Production modal, collapsible sidebar | `54e84d0`, `4ee2101` area |
| Health live / system subprocess | `92c0391`, hooks fixes |
| `prod_url` erp-guarantie / mix-proj | `config/projects/*.yaml` in tree |
| Nextcloud submodule | `nextcloud/` gitlink @ `172a34d4`, yaml `local_path: stage-deployer/nextcloud` |
| Project archive bulk | `4ee2101` |

## Runtime (no new git on target)

| Action | Result |
|---|---|
| `deploy-project.sh nextcloud stage` | ✓ containers up; LDAP test warn |
| `/opt/nextcloud` → symlink | ✓ → `stage-deployer/nextcloud` |
| stage-deployer container rebuild | ✓ (during session) |

## Follow-ups

- Push 15 commits to GitLab origin
- Push `infra/nextcloud.git` submodule remote
- Webhook secrets + SMS `.env`
- See [cursor/todos/2026-06-18-stage-deployer-production-ui-chat-open.md](../cursor/todos/2026-06-18-stage-deployer-production-ui-chat-open.md)
