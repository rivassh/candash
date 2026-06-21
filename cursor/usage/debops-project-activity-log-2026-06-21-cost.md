# Cursor usage cost - debops project activity log

<div dir="rtl" style="text-align: right;">

## Source

- Fresh Cursor Usage CSV was not fetched during this closeout.
- No browser cookie was requested or pasted.
- Exact token/cost accounting remains pending until a fresh Cursor Usage CSV is imported through the existing llfs workflow.

## Best-Effort Prompt Buckets

| Date | Prompt topic | Coverage | Notes |
|------|--------------|----------|-------|
| 2026-06-14 | Sanaradyab/Zabbix/MikroTik/OpenVPN/VoIP/Nextcloud operations | not_measured | Large operational chat, multiple production changes and diagnostics. |
| 2026-06-16 | Project activity log generation | not_measured | Transcript extraction and grouping into work-log rows. |
| 2026-06-21 | llfs closeout | not_measured | Closeout docs, TODOs, prompt summary, cost note, daily report. |

## Follow-up for Exact Cost

Use the existing llfs workflow when a fresh Cursor Usage export is available:

```bash
bash llfs/cursor/scripts/fetch-cursor-usage-csv.sh 2026-06-14 2026-06-21 \
  > llfs/cursor/usage/2026-06-14_2026-06-21.csv
```

Then bucket events for `debops-project-activity-log`.

</div>
