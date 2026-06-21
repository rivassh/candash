# Cursor usage cost — debops activity log (best effort)

<div dir="rtl" style="text-align: right;">

## Source

- Fresh CSV fetch attempted: `bash llfs/cursor/scripts/fetch-cursor-usage-csv.sh 2026-06-13 2026-06-21`
- `~/.config/cursor-usage.env` **????? ????** — ???? cookie ? ???? ??????? ?? ?????.
- Transcript `1241c6d0`: ~946 JSONL lines; ???? ~1MB — ????? ???? pending ?? import CSV.

## Prompt buckets

| Date | Prompt topic | Coverage | Notes |
|------|--------------|----------|-------|
| 2026-06-13 | WAN outage / policy routing / quota | not_measured | MikroTik + Zabbix scripts |
| 2026-06-13 | CEO WAN reports + Nextcloud | not_measured | docs + upload |
| 2026-06-13–14 | FreePBX 9902 / ext305 / OpenVPN | not_measured | Long iterative dialplan thread |
| 2026-06-14 | Support queue TSV / analytics | not_measured | CDR exports |
| 2026-06-14 | WordPress sanaradyab / monitoring disk | not_measured | bind mount; disk extend blocked |
| 2026-06-21 | Activity log from full transcript | not_measured | `ACTIVITY-LOG-2026-06-13-14.md` |
| 2026-06-21 | llfs closeout | not_measured | This file |

## Follow-up for exact cost

```bash
# after ~/.config/cursor-usage.env is configured
bash llfs/cursor/scripts/fetch-cursor-usage-csv.sh 2026-06-13 2026-06-21 \
  > llfs/cursor/usage/2026-06-13_2026-06-21-activity-log.csv
```

Then bucket events for conversation window covering transcript `1241c6d0`.

</div>
