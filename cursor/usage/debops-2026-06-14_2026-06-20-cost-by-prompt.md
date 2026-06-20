# Cursor usage/cost report — debops chat

**Workspace:** `/opt/new/debops`  
**Primary chat transcript:** `75045d56-a947-49a1-b7ac-5f006365f58a`  
**Usage source:** Cursor Dashboard CSV export, imported locally as `ai/cursor-usage/imports/usage-events-2026-06-14-20.csv`  
**Dashboard export window:** `2026-06-14T00:00:00Z` to `2026-06-20T23:59:59Z`  
**Strategy:** `tokens`  
**Security note:** browser cookie/session tokens are intentionally excluded from this report.

## Executive Summary

The CSV contains 915 usage events for the account in the export window. The `Cost` column reports `Included` for 905 events and `Free` for 10 errored/no-charge events, so the marginal dollar cost visible in this export is `$0.00`. The practical cost driver was token volume: 809,713,176 total tokens across the account for the 7-day window.

The export does not include a conversation id, so exact per-prompt billing cannot be proven from the CSV alone. The safest attribution is:

- Exact: account-level token usage by timestamp/model/event.
- Approximate: prompt/topic cost attribution by matching prompt timestamps, hook logs, and work phases.

## Account Usage Totals

| Scope | Events | Total tokens | Input no-cache | Cache read | Output tokens | Cost column |
|---|---:|---:|---:|---:|---:|---|
| 2026-06-14..20 | 915 | 809,713,176 | 55,979,725 | 747,845,057 | 4,215,662 | Included/Free |
| 2026-06-20 only | 59 | 48,990,827 | 4,416,652 | 44,219,474 | 207,658 | Included |

## Model Breakdown

| Model | Events | Total tokens |
|---|---:|---:|
| `composer-2.5-fast` | 481 | 381,757,892 |
| `auto` | 260 | 276,937,787 |
| `gpt-5.5-medium` | 60 | 61,358,590 |
| `gpt-5.3-codex` | 62 | 40,910,839 |
| `agent_review` | 21 | 33,016,470 |
| `grok-4.3` | 10 | 14,402,112 |
| `gemini-2.5-flash` | 19 | 1,329,486 |
| `claude-fable-5-thinking-high` | 2 | 0 |

## 2026-06-20 Hour Buckets

| UTC hour | Events | Total tokens | Input no-cache | Cache read | Output | Dominant models |
|---|---:|---:|---:|---:|---:|---|
| 13:00 | 7 | 12,894,992 | 881,176 | 11,821,741 | 53,713 | `auto`, `agent_review` |
| 14:00 | 18 | 18,309,334 | 1,019,029 | 17,233,764 | 56,541 | `auto` |
| 15:00 | 3 | 2,867,208 | 613,996 | 2,235,924 | 17,288 | `auto` |
| 16:00 | 13 | 5,107,522 | 864,220 | 4,218,125 | 25,177 | `auto` |
| 17:00 | 8 | 1,478,976 | 337,629 | 1,124,085 | 8,581 | `auto`, `agent_review` |
| 18:00 | 10 | 8,332,795 | 700,602 | 7,585,835 | 46,358 | `auto` |

## Largest 2026-06-20 Events

| Time UTC | Model | Tokens | Input no-cache | Cache read | Output | Cost |
|---|---|---:|---:|---:|---:|---|
| 13:50:38 | `auto` | 6,415,931 | 347,562 | 6,060,002 | 8,367 | Included |
| 14:39:23 | `auto` | 3,061,355 | 28,773 | 3,025,078 | 7,504 | Included |
| 14:46:48 | `auto` | 2,700,964 | 154,201 | 2,542,016 | 4,747 | Included |
| 16:07:47 | `auto` | 2,682,632 | 243,584 | 2,430,464 | 8,584 | Included |
| 14:44:13 | `auto` | 2,661,327 | 26,395 | 2,627,264 | 7,668 | Included |
| 13:49:35 | `agent_review` | 1,832,600 | 26,718 | 1,702,000 | 16,981 | Included |
| 15:13:46 | `auto` | 1,820,550 | 274,608 | 1,531,809 | 14,133 | Included |
| 14:32:13 | `auto` | 1,470,567 | 123,947 | 1,340,369 | 6,251 | Included |
| 13:48:01 | `auto` | 1,420,301 | 118,408 | 1,295,082 | 6,811 | Included |
| 18:15:29 | `auto` | 1,399,772 | 17,192 | 1,374,816 | 7,764 | Included |

## Prompt/Topic Attribution

This is an engineering attribution, not a billing primitive. It maps user prompts to the main work phases and the closest available usage window.

| Prompt/topic | Date | Outcome | Cost evidence |
|---|---|---|---|
| Monitoring login details and dashboard links | 2026-06-16 | Identified monitoring URLs and SSH access; avoided exposing passwords. | Covered by account-level CSV for 2026-06-16: 83,368,536 tokens |
| Vault login | 2026-06-16 | Verified Vault dev mode and token login flow. | Same daily bucket |
| `guarantee.sana-gps.ir` 502 | 2026-06-16 | Diagnosed Arvan origin mismatch; origin on `31880` healthy. | Same daily bucket |
| Executive health dashboard | 2026-06-16 | Reworked `health.html` and deployed to monitoring portal. | Same daily bucket |
| Nextcloud/LDAP SMS alert | 2026-06-17 | Identified watchdog/cron source and disable path. | Account CSV for 2026-06-17: 58,315,239 tokens |
| Nexus internal and external usage | 2026-06-19 | Documented internal Nexus setup and VPN-based external access. | Account CSV for 2026-06-19: 217,262,358 tokens |
| Cursor usage tracker rollout | 2026-06-19 | Updated playbook/submodule workflow and pushed changes. | Same daily bucket |
| Critical backup plan and web toggle UI | 2026-06-19..20 | Added backup plan, flags API, web UI, deploy script. | 2026-06-20 account bucket: 48,990,827 tokens |
| Persian encoding fix | 2026-06-20 | Fixed UTF-8 portal pages and SMS JSON headers; deployed to monitoring. | Mostly 13:00..18:00 UTC buckets above |
| Closeout reports to `llfs` | 2026-06-20 | Created TODO, learnings, cost, and daily reports. | Included in final 18:00 UTC usage bucket if exported later |

## Cost-Control Lessons

- Long chats with many production rules and transcript history create very high cache-read volume.
- Use a fresh chat or Ask mode for small explanatory questions.
- Keep CSV exports in `ai/cursor-usage/imports/` and reuse them instead of repeatedly hitting the dashboard API.
- Avoid pasting browser cookies into chat; if pasted, rotate/logout the session afterward.
