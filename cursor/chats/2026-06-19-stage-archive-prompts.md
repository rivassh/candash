# خلاصه پرامپت‌ها — بایگانی پروژه‌های استیج

**تاریخ:** ۱۸–۱۹ ژوئن ۲۰۲۶  
**پروژه:** `/opt/stage-deployer`  
**وضعیت:** پیاده‌سازی انجام شد؛ commit/push و چند follow-up باز مانده

---

## پرامپت ۱ — درخواست اصلی

> همه پروژه‌ها به جز گروه مهدی و modular gps و nextcloud بیان پایین و برن به پوشه آرشیو و برچسب بایگانی بخورن. در صفحه اول پروژه‌ها مهدی پیش‌فرض نشون داده بشه و کاربر با کوکی بتونه انتخاب‌های بعدیش رو اگر از گروه مهدی عوض کرد داشته باشه. پروژه‌های بایگانی‌شده به `/opt/archived` منتقل بشن و اگر کاربر انتخاب کرد از این حالت خارج بشن منتقل شده و deploy شوند.

### خروجی
- `scripts/project_archive.py` — archive / unarchive / bulk-archive
- API: `POST .../archive`, `.../unarchive`, `.../bulk-archive`
- UI: فیلتر بایگانی، برچسب، کوکی گروه مهدی، دکمه بازیابی
- ۲۹ پروژه بایگانی؛ ۸ فعال (keep list)

---

## پرامپت ۲ — گزارش امروز

> گزارش امروز

خلاصه وضعیت استیج، URLها، و موارد باز (modular-gps 502، minicrm-sms دوباره فعال).

---

## پرامپت ۳ — بستن چت + مستندات + llfs

> TODO باقی‌مانده، یادگیری‌ها، مخارج Cursor، push به llfs، گزارش روزانه git

---

## چیزهایی که احتمالاً نمی‌دانستی (یادگیری)

### معماری stage-deployer
1. **لیست پروژه‌ها سه‌لایه است:** پوشه‌های `/opt` + YAML در `config/projects/` + گروه UI در SQLite (`data/project-groups.db`).
2. **گروه «مهدی» فقط در UI است** — پوشه `/opt/mehdi` سایت جداست و در deployer ignore شده.
3. **`enabled: false` ≠ archived** — disabled از config حذف می‌شود؛ archived باید explicit باشد.

### بایگانی
4. **`local_path` nested** (مثل `cms/component-bank`) باید قبل از parent (`cms`) archive شود.
5. **YAMLهای root-owned** فقط از داخل container (`/config/projects`) قابل نوشتن هستند.
6. **nginx** پروژه‌های `archived: true` را از `stage-paths.inc` حذف می‌کند.

### عملیات
7. **بعد از `docker compose restart stage_deployer`** ممکن است `https://.../stage/` 502 بدهد — nginx IP قدیمی container را cache کرده؛ **`nginx -s reload`** لازم است.
8. **`AUTO_STAGE_BOOTSTRAP`** می‌تواند پروژه‌ای مثل minicrm-sms را بعد از بایگانی دوباره up کند.

### UI
9. **localStorage → کوکی** برای فیلتر گروه: انتخاب بین session/device پایدارتر است (path=/).

---

## TODO باز (از این چت)

→ [`../TODO.md`](../TODO.md)
