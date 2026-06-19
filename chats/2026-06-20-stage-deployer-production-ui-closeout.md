# Stage-deployer production / UI / webhook chat closeout — 2026-06-20

**Workspace:** `/opt/stage-deployer` (+ `/opt/nextcloud` → submodule)  
**Transcript:** `a07970af-450e-48d5-8951-3c1007dc6453`

## Prompt summary

| # | Theme | Result |
|---:|---|---|
| 1 | Production auto on `release*` + per-project server/domain + UI | deploy-prod, GitHub mirror, Production API/modal |
| 2 | Cursor Cloud + GitHub | مستند عملیاتی در پاسخ چت |
| 3 | GitLab hook errors + SMS alarm + health | `alert_notify.py`, webhook always 200, health fixes |
| 4 | UI یکپارچه + prod URLs guarantie/crm | projects hub grid, bulk actions, yaml |
| 5 | Sidebar collapsible | `sidebar-collapsed` + localStorage |
| 6 | Nextcloud submodule | `nextcloud/` under stage-deployer |
| 7 | Closeout | TODO + cost + llfs + learnings |

## What you can learn (چیزهایی که شاید «بلد نبودی»)

### 1. مدل stage-deployer (مرزها)

| سؤال | جواب کوتاه |
|---|---|
| کجا Production تنظیم می‌شود؟ | UI → **پروژه‌ها** → دکمه **Production** در هر ردیف؛ یا `config/projects/{slug}.yaml` (`prod_url`, `prod_host`, …) |
| چرا دو لیست «پروژه» و «Stage» گیج‌کننده بود؟ | هر دو همان داده بود — **ادغام شد** در یک grid |
| push به `stage-deployer` خودش stage را deploy می‌کند؟ | **خیر** — webhook برای slugهای **ثبت‌شده** است؛ تغییرات UI/hooks با rebuild/restart deployer اعمال می‌شود |
| Nextcloud کجا زندگی می‌کند؟ | submodule `stage-deployer/nextcloud` → `/opt/stage-deployer/nextcloud` (symlink `/opt/nextcloud`) |

### 2. Webhook و CI

- GitLab **System/Project Hook** اگر **403** بدهد → تقریباً همیشه **secret mismatch** است، نه باگ deploy.
- بعد از fix این چت: پاسخ webhook **همیشه 200** — خطا داخل JSON؛ **pipeline GitLab قرمز نمی‌شود** فقط به خاطر hook.
- Mattermost **پیش‌فرض خاموش**؛ SMS نیاز به `ALERT_SMS_HTTP_URL` + `WEBHOOK_ALERT_SMS_MOBILE` دارد.

### 3. Production deploy

- تگ `release*` (پیشوند از `PRODUCTION_TAG_PREFIX`) → `deploy-prod-safe.sh` → SSH + make target.
- **`prod_url` فقط نمایش/لینک** است؛ deploy واقعی بدون `prod_host` + `prod_path` (یا env CI) انجام نمی‌شود.

### 4. Cursor Cloud + GitHub

- Agent فقط **rule/README داخل همان GitHub repo** را می‌خواند — stage-deployer ruleها را نمی‌بیند.
- جریان: push branch → webhook stage؛ push tag `release*` → production (اگر yaml + GitHub source ست باشد).

### 5. Submodule pattern (مثل `devops`)

```bash
git submodule update --init nextcloud devops
```

- stack compose **داخل submodule** (`stage_compose_in_repo: true`) — template deploy دیگر override را overwrite نمی‌کند.
- remote باید روی GitLab ساخته و push شود وگرنه clone تمیز روی سرور جدید manual است.

### 6. هزینه و امنیت Cursor

- Export CSV: هزینه USD اغلب **Included** — توکن زیاد ≠ خط روی فاکتور.
- **Cookie/session را در چat نفرست** (در این thread و closeout دوباره paste شد) → logout/login Dashboard.

## Security

- Session token در پرامپت closeout — در llfs commit **نشده**. چرخش session توصیه می‌شود.

## Links

- TODO: [cursor/todos/2026-06-18-stage-deployer-production-ui-chat-open.md](../cursor/todos/2026-06-18-stage-deployer-production-ui-chat-open.md)
- Cost: [cursor/usage/stage-deployer-production-ui-2026-06-18_2026-06-20-cost-by-prompt.md](../cursor/usage/stage-deployer-production-ui-2026-06-18_2026-06-20-cost-by-prompt.md)
- Git daily: [reports/2026-06-18-20-stage-deployer-production-ui-git-daily.md](../reports/2026-06-18-20-stage-deployer-production-ui-git-daily.md)
