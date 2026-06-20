# Cursor usage/cost — Passbolt chat — 2026-06-19

**Workspace:** `/opt/new/debops`  
**Transcript:** `9b52136b-ac4b-4a73-973d-697cd6c1e004`  
**Dashboard window requested:** `2026-06-14T00:00:00Z` … `2026-06-20T23:59:59Z` (`strategy=tokens`)  
**CSV source used:** `cursor/usage/2026-06-14_2026-06-20.csv` (local import)  
**Live API fetch:** failed — session cookie returned WorkOS auth redirect (expired/invalid)  
**Security:** no browser cookies or session tokens are stored in this repo.

## Executive Summary

| Scope | Events | Total tokens | Cash cost in CSV |
|---|---:|---:|---|
| Account, 2026-06-14..20 | 915 | 809,713,176 | `$0.00` (905× Included, 10× Free) |
| Account, 2026-06-19 only | 158 | 217,262,358 | Included |
| **This Passbolt chat (est.)** | ~2 user prompts + tool turns | not isolated in CSV | Included |

The Cursor export does **not** include conversation IDs, so per-prompt dollar/token billing for this chat cannot be proven exactly. Engineering attribution below is by date bucket + work phase.

## This Chat — Prompt Attribution (approximate)

| Prompt | Topic | Work performed | Cost evidence |
|---|---|---|---|
| 1 | Open Passbolt | repo grep, runbook read, SSH to monitoring, curl/DNS probes, route discovery | Falls inside **2026-06-19** account bucket: **217,262,358 tokens** total that day across all chats |
| 2 | Close chat + llfs export | TODO, learnings, cost/daily docs, git push to `llfs` | Same day bucket; marginal add not separately exported yet |

**Practical takeaway:** this chat was small compared with the 2026-06-19 daily total, but the day itself was heavy because of other long debops chats (Nexus, backups, Cursor tracker, etc.).

## 2026-06-19 Model Breakdown (account-level)

| Model | Tokens |
|---|---:|
| `auto` | 150,169,746 |
| `gpt-5.5-medium` | 25,466,797 |
| `agent_review` | 24,744,038 |
| `composer-2.5-fast` | 16,881,777 |

## How To Refresh The CSV

```bash
# From an authenticated Cursor browser session (do NOT commit cookies):
curl 'https://cursor.com/api/dashboard/export-usage-events-csv?startDate=1781395200000&endDate=1781999999999&strategy=tokens' \
  --compressed -o cursor/usage/2026-06-14_2026-06-20.csv
```

If the response is HTML/redirect to `api.workos.com`, log out/in to Cursor and retry.

## Related Prompt Topics on 2026-06-19 (same cost bucket)

From earlier debops chats in the same day:

- Internal Nexus usage and VPN path for external developers
- Cursor usage tracker rollout (`commit push`)
- Existing backups audit + daily critical backup plan
- Web UI to toggle backup jobs

See also: `cursor/usage/debops-2026-06-14_2026-06-20-cost-by-prompt.md`
