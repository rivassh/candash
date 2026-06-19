# Cursor usage/cost — stage-deployer production / UI / webhook chat

**Workspace:** `/opt` (mainly `stage-deployer`)  
**Transcript:** `a07970af-450e-48d5-8951-3c1007dc6453`  
**CSV:** `cursor/usage/2026-06-14_2026-06-20.csv` (same export window as Dashboard)  
**Export window:** `2026-06-14T00:00:00Z` – `2026-06-20T23:59:59Z` (ms: `1781395200000`–`1781999999999`)  
**Strategy:** `tokens`  
**Security:** no cookies or session tokens stored in this repo.

## Executive summary

| Scope | Events | Total tokens | Cost column |
|---|---:|---:|---|
| Full CSV (7 days, all `/opt` chats) | 930 | 826,955,860 | Included / Free |
| **2026-06-18** (heavy stage-deployer day) | 35 | 43,682,572 | Included |
| **2026-06-19** | 158 | 217,262,358 | Included |
| **2026-06-20** (archive + closeout) | 74 | 66,233,511 | Included |
| **Jun 18–20 subtotal** | 267 | **327,178,441** | Included |

**Dollar cost in CSV:** `$0.00` marginal — ستون `Cost` = `Included` / `Free`.  
**هزینه عملی:** حجم توکن + پلن؛ CSV **conversation id ندارد** → هزینه per-prompt **تقریبی** است.

> این چت روی VM چند session و هم‌پوشانی با چت‌های دیگر (health/deploy، archive) دارد؛ اعداد بالا سقف workspace است نه فقط یک transcript.

## Model breakdown (Jun 18–20)

| Model | Events | Total tokens |
|---|---:|---:|
| `auto` | 210 | 236,206,710 |
| `composer-2.5-fast` | 24 | 37,169,000 |
| `gpt-5.5-medium` | 17 | 29,402,099 |
| `agent_review` | 16 | 24,848,632 |

## Peak UTC hours (Jun 18 — start of this thread)

| Hour (UTC) | Tokens |
|---:|---:|
| 17 | 12,623,665 |
| 12 | 9,857,730 |
| 20 | 9,666,327 |
| 18 | 5,175,964 |

## User prompts ↔ work phases (approximate)

| # | Prompt theme | Delivered |
|---:|---|---|
| 1 | `release*` → production + per-project prod host/domain + UI light/mobile | API, deploy-prod, GitHub release, UI production panel |
| 2 | Cursor Cloud → GitHub repos | راهنمای rule + webhook (بدون کد) |
| 3 | GitLab hook #1597 + alarms + health live | SMS alert, webhook 200, health fixes |
| 4 | SMS not Mattermost; UI merge grid; guarantie/crm prod URLs | hub UI, bulk actions, yaml prod_url |
| 5 | Sidebar collapsible | toggle + localStorage |
| 6 | Nextcloud as submodule | `nextcloud/` submodule, deploy path, symlink |
| 7 | Closeout: TODO, learnings, cost, llfs | this file + push |

## Habits to reduce cost next time

1. **یک چت = یک موضوع** — production UI جدا از health/deploy جدا از archive.
2. **Cookie export:** `cursor/scripts/fetch-cursor-usage-csv.sh` + `~/.config/cursor-usage.env` — **هرگز cookie در چت نفرست.**
3. **Push زودتر** — ۱۵ commit unpushed = session‌های طولانی بعدی برای همان repo.
4. **Scope صریح** — «فقط yaml» vs «UI + deploy + doc» قبل از شروع.

## How to refresh CSV

```bash
# once: echo "CURSOR_USAGE_COOKIE='...'" > ~/.config/cursor-usage.env && chmod 600 ...
bash /opt/llfs/cursor/scripts/fetch-cursor-usage-csv.sh 2026-06-14 2026-06-20 \
  > /opt/llfs/cursor/usage/2026-06-14_2026-06-20.csv
```
