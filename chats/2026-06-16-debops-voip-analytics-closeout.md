# Closeout — VoIP 9902 / analytics / encoding

**Transcript:** `863b2d6c-72f7-447c-afc7-e3adc62197fd` · **debops:** `/opt/new/debops`

## Learnings (چیزهایی که احتمالاً نمی‌دانستید)

1. **override FreePBX شکننده است** — deploy تکراری survey/position فایل را خراب می‌کند؛ فقط override کوچک + stock.
2. **Survey بعد از Queue() اغلب اجرا نمی‌شود** — log: `COMPLETEAGENT=0` و خروج به `h`.
3. **اعضای صف UI ≠ repo** — 204/205 vs 302–305 → «پاسخگو نیست».
4. **Metabase (تاریخی+WP) جدا از Grafana (live)** — دو ابزار، دو مسیر داده.
5. **Cursor Write فارسی را `???` می‌کند** — Python UTF-8.
6. **WP match ~4%** — normalize موبایل مهم است.
7. **Grafana JSON** — زیر `provisioning/dashboards/json/` نه volume روی RO tree.
8. **Cookie Cursor در چت** — rotate session (این closeout هم cookie داشت).

## Done

rollback stock 9902 · call_events_enriched · Grafana plugin deploy · Makefile · UTF-8 sweep · CREDENTIALS labels

## Open

Metabase dashboard UI · wallboard verify · `.env` · debops commit split
