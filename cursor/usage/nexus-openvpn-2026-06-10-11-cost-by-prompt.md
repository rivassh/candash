# Cursor usage/cost — Nexus + OpenVPN chat

**Transcript:** `82328549-acd5-4f23-9467-1f30d653c78c`  
**Chat dates:** 2026-06-10 (20:11 +0330)  
**User curl window:** 2026-06-14 .. 2026-06-20 (`1781395200000`–`1781999999999`)

## Fetch status

| Attempt | Result |
|---|---|
| Live API curl (cookie in chat) | **307** WorkOS redirect — session expired |
| Window 2026-06-14..20 | **Does not cover** this chat (2026-06-10) |
| CSV `2026-06-14_2026-06-20.csv` | **0 rows** for 2026-06-10/11 |

**Per-prompt cost for this chat: not computable** until CSV imported for 2026-06-10..11.

## Refresh (no cookies in git)

```bash
# ~/.config/cursor-usage.env
curl 'https://cursor.com/api/dashboard/export-usage-events-csv?startDate=1781037000000&endDate=1781209799999&strategy=tokens' \
  -H "Cookie: $CURSOR_USAGE_COOKIE" \
  -o llfs/cursor/usage/2026-06-10_2026-06-11.csv
```

## Phase attribution (engineering)

| Phase | Why tokens add up |
|---|---|
| Nexus recovery | SSH, DB reset, REST EULA, repo loops |
| Client rollout | Multi-host SSH, sudo/CA/docker |
| DNS | WinRM + MikroTik attempts |
| NC OpenVPN app | PHP/JS app + MikrotikService |

## Reference — account 2026-06-14..20

809,713,176 tokens total; Cost column **Included** ($0 marginal). See `summary-2026-06-14_2026-06-20.md`.

**Security:** rotate Cursor session — cookie pasted in chat.
