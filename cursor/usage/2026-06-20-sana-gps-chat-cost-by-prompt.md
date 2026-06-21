# Cursor cost breakdown by prompt (estimated) � sana-gps chat

Source used: `curl .../export-usage-events-csv?startDate=1781395200000&endDate=1781999999999&strategy=tokens`  
Fetched at: 2026-06-20 (local run)  
Raw rows: 962 (`2026-06-14T04:08:39Z` .. `2026-06-20T19:36:44Z`)

## Important limitation

Cursor CSV export has no conversation-id column in this dataset, so **exact invoice-grade per-prompt billing is not available**.
The table below is an engineering attribution built from:

- prompt timeline in this chat
- UTC windows matching those prompt times
- tool-call intensity per phase

## Window totals used for attribution

| Window (UTC) | Events | Tokens |
|---|---:|---:|
| 2026-06-14 06:30..08:30 | 68 | 71,072,889 |
| 2026-06-16 06:30..07:30 | 30 | 24,105,328 |
| 2026-06-20 19:00..20:00 | 37 | 35,653,987 |

CSV `Cost` values in this export are `Included` (and a few `Errored, No Charge`), so marginal billed cost appears as **$0** in the export.

## Per-prompt attribution (approx.)

| # | Prompt cluster | Date | Estimated tokens | Cost label |
|---|---|---|---:|---|
| 1 | Diagnose `502` and isolate server vs CDN/origin | 2026-06-14 | 17,768,222 | Included |
| 2 | Verify MikroTik backups + radio/fiber path assumptions | 2026-06-14 | 10,660,933 | Included |
| 3 | Live MikroTik recovery and NAT correction (`80/443`, `8089`) | 2026-06-14 | 24,875,511 | Included |
| 4 | Restore management baseline (`sysadmin`, service alignment) | 2026-06-14 | 7,107,289 | Included |
| 5 | Build/deploy outage watchdog (SMS then call) | 2026-06-14 | 10,660,933 | Included |
| 6 | Generate activity log table from full chat history | 2026-06-16 | 24,105,328 | Included |
| 7 | Closeout package: carry unresolved TODOs, learning notes, cost docs, daily report, push to `llfs` | 2026-06-20 | 35,653,987 | Included |

Estimated attributed total for this chat package: **130,832,203 tokens**.

## Model distribution in closeout window (2026-06-20 19:00..20:00 UTC)

| Model | Tokens |
|---|---:|
| `auto` | 18,305,845 |
| `composer-2.5-fast` | 15,322,235 |
| `gpt-5.3-codex` | 1,091,654 |
| `gpt-5.5-medium` | 798,041 |
| `gemini-2.5-flash` | 136,212 |

## Security note

Web session cookie/token appeared in prompt context. Rotate Cursor web session after export.
