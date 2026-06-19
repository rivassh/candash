# Cursor usage/cost — stage health & deploy chat

**Workspace:** `/opt`  
**Transcript:** `a93bbde8-393a-443d-a2a2-379651d03f35`  
**CSV:** `cursor/usage/2026-06-18_2026-06-20-stage-health.csv`  
**Export window:** `2026-06-14T00:00:00Z` – `2026-06-20T23:59:59Z` (ms: 1781395200000–1781999999999)  
**Strategy:** `tokens`  
**Security:** no cookies or session tokens stored in this repo.

## Executive summary

| Scope | Events | Total tokens | Cost column |
|---|---:|---:|---|
| Full CSV (7 days) | 932 | ~329M+ | Included / Free |
| 2026-06-18 | 35 | 43,682,572 | Included |
| 2026-06-19 (main chat day) | 158 | 217,262,358 | Included |
| 2026-06-20 | 76 | 68,681,833 | Included |
| Approx. session window (Jun 18 PM + Jun 19) | 193 | ~261M | Included |

**Dollar cost in CSV:** `$0.00` marginal (all `Included` in this export). Practical cost = token volume + subscription tier, not line-item USD.

CSV has **no conversation id** — per-prompt cost is approximate (time/model correlation only).

## Model breakdown (Jun 18–20)

| Model | Events | Total tokens |
|---|---:|---:|
| `auto` | 212 | 236,206,710 |
| `composer-2.5-fast` | 24 | 37,169,000 |
| `gpt-5.5-medium` | 17 | 29,402,099 |
| `agent_review` | 16 | 26,848,954 |

## Jun 19 peak UTC hours (tokens)

| Hour | Tokens |
|---:|---:|
| 16 | 28,888,798 |
| 06 | 27,319,317 |
| 15 | 22,233,597 |
| 14 | 21,857,996 |
| 07 | 16,411,772 |

## Prompt themes ↔ work phases (approximate)

| Phase | User intent | Ops note |
|---|---|---|
| Health audit | همه پروژه‌ها | API `/stage/api/projects` + probes |
| Deploy fixes | sanaradyab, mix-proj, cbrender, voip-tts, render-engine | 3 deploys OK; modular-gps failed |
| Nexus advisory | mirror داخلی | doc only |
| sanaradyab-ws 502 | عیب‌یابی | upstream 18059 |
| GitLab CI | runner/k8s yaml | advisory |
| Closeout | TODO, cost, llfs | this report |

## Habits to reduce cost next time

- چت جدید برای موضوع unrelated (Nexus vs deploy vs CI).
- scope کوچک‌تر در multitask (یک subagent قطع شد).
- export CSV یک‌بار؛ تکرار curl با cookie در چت نفرست.
