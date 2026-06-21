# Cursor usage — debops-sana-gps-nat — 2026-06-14 … 2026-06-21

**Generated:** 2026-06-21T11:14:43Z
**CSV:** failed — set CURSOR_USAGE_COOKIE in ~/.config/cursor-usage.env (never commit)
**Security:** no cookies in this repo.

## Source status

- The pasted curl range decodes to `2026-06-14T00:00:00Z` … `2026-06-20T23:59:59.999Z`.
- A local CSV already exists at `cursor/usage/2026-06-14_2026-06-20.csv`.
- The pasted browser cookie/session token was not executed, stored, or committed.
- Exact cost per natural-language prompt is not derivable from this CSV alone because usage events are not linked to chat prompt text.

## CSV summary — 2026-06-14 … 2026-06-20

| Metric | Value |
|--------|------:|
| Usage events | 930 |
| Included events | 920 |
| Paid events | 10 |
| Parsed paid cost | 0 |
| Total tokens | 826,955,860 |
| Input without cache write | 57,411,913 |
| Cache read | 763,563,767 |
| Input with cache write | 1,672,732 |
| Output tokens | 4,307,448 |

## Daily event counts

| Date | Events |
|------|------:|
| 2026-06-14 | 309 |
| 2026-06-15 | 131 |
| 2026-06-16 | 139 |
| 2026-06-17 | 84 |
| 2026-06-18 | 35 |
| 2026-06-19 | 158 |
| 2026-06-20 | 74 |

## 2026-06-14 usage slice

| Metric | Value |
|--------|------:|
| Events | 309 |
| Total tokens | 259,318,757 |
| Input without cache write | 19,089,600 |
| Cache read | 238,759,288 |
| Output tokens | 1,469,869 |

Models on 2026-06-14: `composer-2.5-fast` 212, `gpt-5.3-codex` 59, `gpt-5.5-medium` 28, `grok-4.3` 9, `claude-fable-5-thinking-high` 1.

## Refresh CSV

```bash
bash /home/hamid/.local/share/llfs/cursor/scripts/fetch-cursor-usage-csv.sh 2026-06-14 2026-06-21 \
  > cursor/usage/2026-06-14_2026-06-21.csv
```

## Prompt-level notes

- `Sana GPS outage investigation/fix`: exact prompt cost unavailable; likely represented within the 2026-06-14 usage slice.
- `Activity log request`: exact prompt cost unavailable; requires a CSV that includes 2026-06-16 and a mapping from usage events to chat turns.
- `llfs close explanation`: not included in the existing 2026-06-14_2026-06-20 CSV because it happened on 2026-06-21.

