# Prompts — debops-sana-gps-nat — 2026-06-21

**Do not paste Cursor cookies here.**

## AGENT — short prompt summary (optional)

- 2026-06-14 — کاربر گزارش داد `admin.sana-gps.ir` هیچ دستگاهی را online نشان نمی‌دهد و لیست هم نمی‌آید، فقط روی نقشه دیده می‌شوند.
- 2026-06-16 — کاربر activity log پروژه را از تاریخچه چت خواست؛ خروجی به شکل جدول کاری تولید شد.
- 2026-06-21 — کاربر `llfs close TOPIC` را ارسال کرد؛ ابتدا معنی آن نامشخص بود.
- 2026-06-21 — کاربر تعریف کامل `llfs close TOPIC` را توضیح داد: ثبت TODOهای باز، learnings، هزینه Cursor، prompt summary، git daily و push به `git@github.com:rivassh/llfs.git`.

## Technical activity summary

- Sana GPS/Traccar checked through public HTTP, origin API, Docker containers, DB tables, listener ports and MikroTik NAT.
- Missing MikroTik dstnat rules for Traccar listener ports were restored: `5015`, `5023`, `7700` for TCP/UDP to `172.16.1.134`.
- Verification showed new positions and online statuses returning after the NAT fix.
- Cursor usage cost handling was documented without storing or replaying pasted cookies.

