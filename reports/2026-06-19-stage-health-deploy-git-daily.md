# Daily git report — stage health & deploy session

**Date:** 2026-06-19 (Asia/Tehran)  
**Transcript:** `a93bbde8-393a-443d-a2a2-379651d03f35`  
**Focus:** سلامت stage، دیپلوی مجدد، fix bootstrap compose

## Summary

| Repo | Commits (Jun 18–19) | Deploy/runtime on VM |
|------|---------------------|----------------------|
| `stage-deployer` | 8+ on local `main` (۶ ahead unpushed) | deployer + nginx OK |
| `sanaradyab` | version badge, deploy rule | frontend restart → OK |
| `mix-proj` | deploy rule, ERP fixes | app restart → up |
| `modular-gps` | brand, submodules, cursor rules | 2/12 containers, build fail |
| `cbrender` | — | deploy OK Jun 19 |
| `voip-tts` | — | deploy OK Jun 19 |
| `render-engine` | — | deploy OK, API 404 on `/` |

## stage-deployer (local, unpushed highlights)

```
4ee2101 Add project archive system and bulk-archive stage projects
92c0391 Add local stage auto-heal cron and safer deploy health probes
1212f1c fix(deploy): merge base compose with stage override files
```

**Not pushed:** working tree still has UI/webhook/log changes (~35 files).

## Runtime deploys (no git commit on target repos)

Executed via `./scripts/deploy-project.sh`:

| Slug | Result |
|------|--------|
| `cbrender` | ✓ ~16 min build |
| `voip-tts` | ✓ HTTP 200 |
| `render-engine` | ✓ stack 2/2 |
| `sanaradyab` | partial — bootstrap fix; frontend manual start |
| `modular-gps` | ✗ npm build in Docker |
| `mix-proj` | conflict container name; manual `docker start` |

## Health snapshot (end of session)

| Project | Stack | Notes |
|---------|-------|-------|
| sanaradyab | 13/13 | main OK; ws 502 |
| cbrender | 3/3 | OK |
| voip-tts | 2/2 | OK |
| render-engine | 2/2 | API root 404 |
| mix-proj | 2/2 | HTTP 401 |
| modular-gps | 2/12 | 502 |

## Follow-ups

See [cursor/todos/2026-06-19-stage-health-deploy-chat-open.md](../cursor/todos/2026-06-19-stage-health-deploy-chat-open.md)
