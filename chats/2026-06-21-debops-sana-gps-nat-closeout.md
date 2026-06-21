# Chat closeout — debops-sana-gps-nat — 2026-06-21

**Workspace:** `/opt/new/debops`
**Generated:** 2026-06-21T11:14:43Z

## Learnings (AGENT — max 5 bullets)

- `llfs close TOPIC` باید یک closeout کم‌توکن باشد: TODOهای باز، learnings، prompt summary، cost pointer و git daily را در `llfs` بسازد و push کند.
- در اختلال Sana GPS، دیدن خودروها روی نقشه به معنی live بودن ingestion نیست؛ ممکن است فقط آخرین positionهای قدیمی نمایش داده شوند.
- برای Traccar، وضعیت online/list به ورود مداوم داده از listenerها و status table وابسته است، نه صرفاً سلامت HTTP پنل.
- تست درست این incident ترکیب DB (`tc_positions`, `tc_devices`)، listener ports، MikroTik NAT و counters بود.
- Cookie Cursor نباید در چت یا git ذخیره شود؛ هزینه باید از CSV محلی/امن یا `~/.config/cursor-usage.env` استخراج شود.

## Prompt summary (AGENT — optional)

| # | Theme | Result |
|---:|---|---|
| 1 | Sana GPS outage | بررسی شد که `admin.sana-gps.ir` HTTP 200 می‌دهد اما list/online خالی است. |
| 2 | Root cause | Traccar DB نشان داد `online=0` و position جدید پس از حدود `09:37` ثبت نشده بود. |
| 3 | Network fix | NAT پورت‌های `5015`, `5023`, `7700` برای TCP/UDP به `172.16.1.134` روی MikroTik بازگردانده شد. |
| 4 | Verification | پس از fix، position جدید و online deviceها برگشتند و MikroTik backup قبل/بعد گرفته شد. |
| 5 | Activity log | از تاریخچه چت، activity log پروژه با ستون‌های تاریخ/وضعیت زمانی/موضوع/توضیح ساخته شد. |
| 6 | llfs meaning | معنی `llfs close TOPIC` روشن شد و این closeout طبق همان workflow ساخته شد. |

## Artifacts

| Type | Path |
|------|------|
| Git daily | [`reports/2026-06-21-debops-sana-gps-nat-git-daily.md`](../reports/2026-06-21-debops-sana-gps-nat-git-daily.md) |
| Cost | [`cursor/usage/debops-sana-gps-nat-2026-06-14_2026-06-21-cost.md`](../cursor/usage/debops-sana-gps-nat-2026-06-14_2026-06-21-cost.md) |
| TODO | [`cursor/todos/2026-06-21-debops-sana-gps-nat-chat-open.md`](../cursor/todos/2026-06-21-debops-sana-gps-nat-chat-open.md) |
| Prompts | [`cursor/chats/2026-06-21-debops-sana-gps-nat-prompts.md`](../cursor/chats/2026-06-21-debops-sana-gps-nat-prompts.md) |

## Security

- Never commit Cursor session cookies.
- CSV: `failed — set CURSOR_USAGE_COOKIE in ~/.config/cursor-usage.env (never commit)`

