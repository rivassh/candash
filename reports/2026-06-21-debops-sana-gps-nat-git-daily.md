# گزارش Git — debops-sana-gps-nat — 2026-06-21

**Topic:** debops-sana-gps-nat
**Workspace:** `/opt/new/debops`
**Generated:** 2026-06-21T11:14:43Z

## Activity log

| Date | Timing | Topic | Technical note |
|------|--------|-------|----------------|
| 2026-06-14 | Office hours | Sana GPS Traccar outage | Investigated public admin UI, origin API, Docker services, Traccar DB and MikroTik NAT; restored missing listener NAT for `5015`, `5023`, `7700` TCP/UDP to `172.16.1.134`; verified new positions and online devices returned. |
| 2026-06-16 | Office hours | Project activity log | Produced a concise work-log table from chat history with date, timing, topic and short technical detail. |
| 2026-06-21 | Office hours | llfs close workflow | Documented `llfs close TOPIC` semantics, generated closeout artifacts, TODOs, prompt summary, cost notes and this git daily report. |

### Git — debops

**Path:** `/opt/new/debops`

**Branch:** `main` → `origin/main`

#### Commits (2026-06-14 … 2026-06-21)

_No commits in range._

#### Working tree

```
## main...origin/main [ahead 1, behind 3]
M  .gitmodules
 M Makefile
 M ai/cursor-usage/LAST-PROMPT.md
 M ai/cursor-usage/README.md
 M ai/cursor-usage/SUMMARY.md
 M ai/cursor-usage/agent-state.json
 M ai/cursor-usage/last-prompt.json
 M ai/cursor-usage/ledger.jsonl
 M ai/cursor-usage/prompt-log.jsonl
 m camera
 M docs/backup/DAILY-CRITICAL-BACKUP-PLAN.md
 M docs/backup/daily-critical-backup.env.example
AM llfs
 M scripts/backup/daily-critical-backup.sh
 M scripts/stage-deployer/deploy-path-safe.sh
 M scripts/stage-deployer/install-stage-deploy-guard.sh
 M scripts/stage-deployer/stage-project-auto-heal.sh
 M scripts/stage-deployer/stage-project-health-url.sh
 M ssh.configs
```

## llfs

- Closeout: [`chats/2026-06-21-debops-sana-gps-nat-closeout.md`](../chats/2026-06-21-debops-sana-gps-nat-closeout.md)
- Cost: [`cursor/usage/debops-sana-gps-nat-2026-06-14_2026-06-21-cost.md`](../cursor/usage/debops-sana-gps-nat-2026-06-14_2026-06-21-cost.md)
- TODO: [`cursor/todos/2026-06-21-debops-sana-gps-nat-chat-open.md`](../cursor/todos/2026-06-21-debops-sana-gps-nat-chat-open.md)
- Prompts: [`cursor/chats/2026-06-21-debops-sana-gps-nat-prompts.md`](../cursor/chats/2026-06-21-debops-sana-gps-nat-prompts.md)

