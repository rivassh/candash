# Cursor usage/cost � network + GitLab + stage Docker chat

**Workspace:** `/opt/new/debops`  
**Conversation (hook log):** `6d0b8c37-2132-40c1-ba75-a938dcecabb0`  
**Usage source:** local import `debops/ai/cursor-usage/imports/usage-events-2026-06-14-20.csv`  
**Dashboard window:** `2026-06-14` .. `2026-06-20` (strategy: tokens)  
**Live curl export:** failed (HTTP 307 ? auth); **do not commit cookies**

## Executive summary

| Metric | Value |
|--------|------:|
| Account tokens **2026-06-20** (full day, CSV) | 48,990,827 |
| Account tokens **2026-06-18** (full day, CSV) | 43,682,572 |
| CSV last event | `2026-06-20T18:22:57Z` (before closeout prompts) |
| **Cost column in CSV** | `Included` / `Free` ? **$0 marginal** in export |
| Transcript hook estimate (conv `6d0b8c37`, 3 entries) | ~43,844 tokens (transcript bytes � 4, not billing) |

CSV has **no conversation id** � per-prompt costs below are **engineering attribution**, not invoice lines.

## Closest API window (agent-heavy diagnostics)

| UTC window | Events | Total tokens | Notes |
|------------|-------:|-------------:|-------|
| 2026-06-20 17:00�18:23 | 18 | 9,811,771 | overlaps SSH/diagnosis/deploy phase |
| 2026-06-20 full day | 59 | 48,990,827 | includes other debops work same day |

## Per-prompt attribution (approximate)

| # | Prompt topic | Phase | Attributed share | Tokens (est.) | Cost |
|---|--------------|-------|----------------:|--------------:|------|
| 1 | Where to see network load | docs / repo read | ~5% of chat window | ~490k | Included |
| 2 | Winbox rule for GitLab | docs / backup read | ~5% | ~490k | Included |
| 3 | Ping still fails from stage | SSH live debug | ~35% | ~3.4M | Included |
| 4 | Prevention + implement guard | scripts + stage deploy + docker incident | ~45% | ~4.4M | Included |
| 5 | Close chat + llfs docs | write + git push | ~10% | ~980k | Included |

**Basis for split:** prompt order, tool-call intensity, and 9.8M-token window 17:00�18:23 UTC on 2026-06-20.

## Models (2026-06-20 account day)

Dominant: `auto`, `composer-2.5-fast` (see `summary-2026-06-14_2026-06-20.md`).

## Operational cost (non-Cursor)

- ~2 min Docker outage on stage during bad `daemon.json` test � **reverted**
- No MikroTik config backup run after user-added filter rule

## Refresh CSV safely

```bash
# ~/.config/cursor-usage.env � never commit
bash debops/llfs/cursor/scripts/fetch-cursor-usage-csv.sh 2026-06-14 2026-06-20 \
  > llfs/cursor/usage/2026-06-14_2026-06-20.csv
```

## Security

Session cookie was pasted in chat � **rotate Cursor web session** (logout/login).
