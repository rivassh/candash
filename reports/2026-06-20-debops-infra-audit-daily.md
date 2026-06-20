# Daily git report — debops infra audit — 2026-06-20

**Repo:** `/opt/new/debops`  
**Chat:** `4a1a1a44-0d4d-4fdd-95e9-74382547d867`  
**Focus:** forensic audit FreePBX + monitoring routine + SMS/HTML portal

---

## Git history (????? ?? ??? ??)

| Date (local +0330) | Commit | Message |
|--------------------|--------|---------|
| 2026-06-20 01:15 | `e8d3564` | `a log changes` |

### ???????? ????? ?? `e8d3564` (infra audit)

| Path | ??? |
|------|-----|
| `docs/reports/infra-audit-2026-06-18/INFRA-AUDIT-REPORT-2026-06-17-19.md` | ????? ??????? forensic |
| `docs/reports/infra-audit-2026-06-18/events.tsv` | timeline ???????? |
| `docs/reports/infra-audit-2026-06-18/freepbx-*.txt` | Apache, SSH, DB, verify |
| `scripts/monitoring/infra-audit-collect.sh` | collector ??????? |
| `scripts/monitoring/deploy-infra-audit.sh` | deploy ?? monitoring |
| `monitoring/portal/audit.html` | UI wallboard |
| `monitoring/portal/infra-audit-pc-map.tsv` | IP ? PC |
| `monitoring/portal/infra-audit.env.example` | allowlist/SMS env |
| `Makefile` | `infra-audit`, `infra-audit-deploy` |

> commit ???? **??? audit ????** — Makefile? backup plan? analytics? reports ???? ? … ?? ?? ???? commit ????????.

---

## Activity log (?? ???? ????????? ? deploy)

| ???? (??????) | ????? | ????? ??? |
|---------------|--------|-----------|
| 2026-06-19 | Forensic request | ??????? ?????: Apache, secure, MikroTik, PC map |
| 2026-06-19 | Live collection | SSH ?? FreePBX? ~43k ?? Apache ??–?? ???? |
| 2026-06-19 | Findings | GUI `.173` (No.20-PC.2) ?? ????? SSH `.143` ??–?? ????? admin password change ?? history |
| 2026-06-19–20 | Monitoring routine | collector + cron `*/15` + `audit-status.json` |
| 2026-06-20 | Portal | `http://172.16.1.164:8090/audit.html` — HTTP 200 |
| 2026-06-20 | SMS path | Novin `172.16.1.134:3021/send-sync` — cooldown + dedupe hash |
| 2026-06-20 | Fixes | allowlist 164? CRLF strip? Docker cp not mv? state.env quoting |

---

## ????????? ??????? (?????)

1. **?? ????:** session GUI ?? `172.16.1.173` — queues/9902? MOH upload? ?? reload.
2. **??–?? ????:** ?????? SSH ????? ?? `.175` (automation)? ???? POST save ???? ?? `.173`.
3. **??–?? ????:** SSH ?????? `root@172.16.1.143` + `UPDATE ampusers` ?? bash_history.
4. **9902:** hybrid state — ?? FAIL ?? verify script.
5. **MikroTik:** live log ?? monitoring ???? WARN (no SSH key).

---

## Verification

| Check | Result |
|-------|--------|
| `curl -s -o /dev/null -w '%{http_code}' http://172.16.1.164:8090/audit.html` | 200 |
| `audit-status.json` overall | OK ?? ?? fix allowlist |
| `make infra-audit` | collector ??? monitoring |

---

## Open work ? TODO

- `cursor/todos/infra-audit-chat-open.md`
- `cursor/TODO.md` (??? Infra Audit)

---

## Related llfs docs

- `chats/2026-06-20-debops-infra-audit-session.md`
- `cursor/usage/debops-infra-audit-chat-2026-06-19-20-cost.md`
