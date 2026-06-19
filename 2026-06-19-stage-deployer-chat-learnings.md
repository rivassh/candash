# یادگیری‌ها و خلاصه چت — stage-deployer (۱۹ ژوئن ۲۰۲۶)

> چت Cursor: workflow استیج → production، webhookها، UI پنل، و پروژه‌های گروه مهدی

---

## چیزهایی که احتمالاً بلد نبودی (یا مدل ذهنی‌ات فرق داشت)

### ۱. GitHub webhook ≠ GitLab webhook

دو endpoint و دو secret جدا:

| مسیر | متغیر `.env` | مکانیزم |
|------|--------------|---------|
| `/webhook/gitlab` | `GITLAB_WEBHOOK_SECRET` | هدر `X-Gitlab-Token` — مقایسه مستقیم |
| `/webhook/github` | `GITHUB_WEBHOOK_SECRET` | هدر `X-Hub-Signature-256` — امضای HMAC روی body |

یکی کردن دو secret در `.env` کافی نیست؛ باید در **تنظیمات webhook همان سرویس** (GitHub repo Settings → Webhooks) هم ثبت شود.

### ۲. جریان GitHub → stage-deployer → GitLab

Push به GitHub فقط **ورودی** stage-deployer است. Secret گیتلب برای push کردن کد به GitLab استفاده **نمی‌شود**؛ آن push با **SSH key** انجام می‌شود. Secret گیتلب فقط وقتی لازم است که **GitLab** به `/webhook/gitlab` event بفرستد.

### ۳. HTTP 200 ≠ موفقیت webhook

stage-deployer عمداً همیشه `hook_http: 200` برمی‌گرداند تا GitHub/GitLab مدام retry نکنند.  
پنل UI محتوای JSON را می‌خواند: `accepted: false` + `error` = رد شده (مثلاً `invalid signature`).

### ۴. GitHub سبز، پنل قرمز

GitHub فقط status code را می‌بیند. پنل stage-deployer outcome واقعی را از body لاگ می‌کند.

### ۵. `branch 'master' excluded` خطا نیست

`STAGE_REF_EXCLUDE` (معمولاً `main,master,develop`) push مستقیم به این شاخه‌ها روی **GitLab webhook** را ignore می‌کند — مگر per-project در مودال 🔀 شاخه GitLab Stage را صریح تنظیم کنی.

### ۶. `STAGE_TAG` حذف نشده — نقشش عوض شده

- هنوز **پیش‌فرض سراسری** در `.env` است.
- هر پروژه می‌تواند در مودال «تگ Stage» override کند.
- مسیر **اصلی Stage** الان **شاخه** است (GitHub/GitLab branch)، نه push تگ `stage`.

### ۷. تغییر `.env` بدون restart اثر ندارد

متغیرهای محیطی فقط در **start کانتینر** خوانده می‌شوند → دکمه «↻ reload سرویس» در سایدبار پنل اضافه شد.

### ۸. ستون «دیپلوی: هرگز»

`deploy-history.json` قبلاً فقط از UI/webhook پر می‌شد؛ deploy از CLI ثبت نمی‌کرد → بعد از fix، backfill لازم بود.

### ۹. `docker-compose` الزاماً در `infrastructure/` نیست

stage-deployer خودش در چند مسیر رایج جستجو می‌کند (`.`, `deploy/`, `infrastructure/`, …) و bootstrap خودکار `.env.stage` می‌سازد.

### ۱۰. نسخه پایین صفحه UI پروژه‌ها

الگوی `mix-proj`: `version-footer` + `/changelog` + rule semver — برای پروژه‌های گروه مهدی یکی‌یکی بررسی و اعمال شد.

---

## خلاصه پرامپت‌های این چت (به ترتیب)

1. **modular-gps-tracking-platform** — اولین کامیت برای stage؛ چه کارهایی کم است؟ آیا بدون پرامپت می‌توان stage/deploy را جلو برد؟
2. پروژه جدید از داشبورد — آیا دستی `.env.stage` لازم است؟
3. می‌خواهم همه‌چیز را خود stage-deployer انجام دهد.
4. آیا compose حتماً باید در `infrastructure` باشد؟
5. این‌ها به صورت راهنما در stage-deployer باشند.
6. (پیام ناقص) «در stage d…»
7. آیا همه پروژه‌های گروه مهدی نسخه پایین UI دارند؟
8. همان سؤال — باید نسخه در UI باشد؛ الان هست؟
9. بررسی در کامیت‌های قبلی / برنچ‌های دیگر (نگران حذف توسط AI).
10. «بگو» (ادامه گزارش)
11. «بگو»
12. «انجام بده» (پیاده‌سازی version footer)
13. push و deploy
14. چرا استیج پایین است؟
15. چطور دوباره پیش نیاید / خودکار حل شود؟
16. «برو» (auto-heal و پایداری)
17. مبهم بودن UI: دیپلوی «هرگز»، webhook سبز ولی قرمز، sidebar sticky
18. GitHub webhook سبز ولی `invalid signature` در پنل — چرا؟
19. GitLab hook log: `branch 'master' excluded` — چرا؟
20. مودال per-project: شاخه GitHub/GitLab برای stage + تگ‌های production
21. عملاً `STAGE_TAG` رفت کنار؟
22. `invalid signature` یعنی چه؟ (توضیح HMAC)
23. فکر می‌کردم secret گیتلب برای گیتهاب است — تصحیح مدل ذهنی
24. دکمه restart کل stage-deployer برای لود `.env`
25. **این پیام** — یادگیری‌ها + آرشیو چت + push به llfs

---

## کارهای انجام‌شده در این چت (مرجع)

- version footer / changelog برای پروژه‌های گروه مهدی
- auto-heal، deploy-history از CLI، sidebar sticky
- مودال 🔀 deploy-routing per project
- دکمه ↻ reload سرویس
- توضیح و تشخیص webhook GitHub vs GitLab

---

## آرشیو چت در Cursor — بعداً دسترسی داری؟

**بله، معمولاً.** Archive در Cursor چت را **حذف نمی‌کند**؛ از لیست فعال خارج می‌شود ولی در **تاریخچه چت‌ها** (Chat History) قابل جستجو و باز کردن است.

نکات:

- به History / حساب Cursor وابسته است — روی دستگاه یا اکانت دیگر ممکن است نباشد.
- برای مستندات ماندگار (مثل این فایل) **git بهتر از تکیه به آرشیو چت** است.
- اگر چت را Delete کنی (نه Archive)، دیگر در دسترس نیست.

---

## meta

- تاریخ: 2026-06-19
- workspace: `/opt` (stage-deployer، پروژه‌های گروه مهدی)
- transcript id: `ba6372af-50b5-46f4-b248-9a3513942f94`
