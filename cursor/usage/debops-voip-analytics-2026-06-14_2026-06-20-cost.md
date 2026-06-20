# Cursor cost — debops VoIP/analytics chat

**CSV:** `cursor/usage/2026-06-14_2026-06-20.csv` (export 2026-06-20)  
**Window:** 2026-06-14 … 2026-06-20 · strategy=tokens  
**Live curl in closeout:** HTTP 307 (session expired) — **no cookie stored**

| Day | Events | Tokens | Notes |
|-----|--------:|-------:|-------|
| 2026-06-14 | 309 | 259,318,757 | FreePBX، TSV |
| 2026-06-15 | 131 | 98,774,887 | reorg، MikroTik |
| 2026-06-16 | 139 | 83,368,536 | rollback، analytics، make |
| **7-day total** | **930** | **826,955,860** | account-wide |

Cost column: Included/Free → $0 marginal in export. No per-conversation id in CSV.
