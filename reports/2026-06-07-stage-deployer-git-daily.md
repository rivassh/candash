# Git daily report — stage-deployer chat

**Date:** 2026-06-07 (closeout)  
**Period covered:** 2026-06-07 – 2026-06-20 (activity during this chat arc)  
**Transcript:** `1f3fb1df-2297-45af-978c-92fc489fd128`  
**Host:** infra2 (`172.16.1.150`, `/opt`)

## Summary

| Repo | Commits (Jun 7–20) | Main themes |
|---|---:|---|
| `stage-deployer` | 15 | webhook log, Nextcloud, domain migrate, GitHub webhook, UI, deploy fixes, AI rules |
| `mix-proj` | 4 | stage deploy rule, AGENTS.md, auth fix |
| `erp-guarantie` | 4 | stage rule, semver/changelog |
| `modular-gps-tracking-platform` | 18+ | web UI themes (parallel work) |
| `artexx`, `callcenter`, `chatbot-website`, `sanaradyab`, … | 2–4 each | stage deploy rule + version badge |
| `llfs` | 18+ | prior chat archives + this closeout |

## stage-deployer (chronological highlights)

| Date | Hash | Subject |
|---|---|---|
| 2026-06-07 | `2577f8f` | Add webhook request logging and stage path deploy model |
| 2026-06-13 | `42ce181` | GitHub preview UI and stage domain migration |
| 2026-06-18 | `95cc36c` | Cursor rule: deploy stage after task completion |
| 2026-06-18 | `1212f1c` | fix(deploy): merge base compose with stage override |
| 2026-06-18 | `3cab41f` | Release 0.7.0 |
| 2026-06-18 | `3879bea` | project hook accordion, deploy pipeline UI |
| 2026-06-18 | `5fd524d` | System Hook + deploy history accuracy |
| 2026-06-19 | `92c0391` | local stage auto-heal cron |
| 2026-06-19 | `54e84d0` | stage UI deploy history + sticky sidebar |
| 2026-06-20 | `4ee2101` | project archive system |

**Not pushed:** several commits on protected `main` (incl. `95cc36c`) — MR needed.

## Application repos — stage AI rules (2026-06-18)

Propagated `.cursor/rules/stage-deploy-after-changes.mdc` + `AGENTS.md` updates:

- mix-proj, erp-guarantie, 25c, 25c2, artexx, callcenter, cbrender, chatbot-website
- map, medical-chatbot, minicrm-sms, modular-gps-tracking-platform, sana-gps, sanaradyab, voip-tts
- cms/component-bank, cms/render_engine

## Prompt ↔ git correlation

| User prompt theme | Git evidence |
|---|---|
| webhook logging | `2577f8f` |
| Nextcloud dashboard | nextcloud submodule + config yaml |
| stage.artandev.ir | `42ce181`, `.env` STAGE_URL_TEMPLATE |
| GitHub auto deploy | github_webhook.py changes (Jun 18) |
| AI deploy rules all /opt | `95cc36c` + 15 project commits |
| mix-proj main ignored | no mix-proj CI commit; webhook config issue |
| GitLab CI red | no CI yaml fixes committed yet |

## Uncommitted / pending

- stage-deployer: local commits ahead of origin (protected main)
- GitLab CI domain/path verify — not started
- System Hook handler — partial (`5fd524d` mentions accuracy fix; full accept TBD)

## Deploy commands used (manual, not in git)

```bash
cd /opt/stage-deployer && ./scripts/deploy-project.sh mix-proj main
cd /opt/stage-deployer && ./scripts/deploy-project.sh nextcloud stage
docker compose -f /opt/stage-deployer/docker-compose.yml restart stage-deployer
```

## Next git actions (from TODO)

1. MR stage-deployer → main on GitLab infra remote
2. Add `.gitlab-ci.yml` to mix-proj, erp-guarantie, cbrender
3. Push this llfs closeout bundle
