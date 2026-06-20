# خلاصه چت Cursor — workspace `/opt/new/artexxpro`

**تاریخ:** ۲۰ ژوئن ۲۰۲۶ (≈ ۳۰ خرداد ۱۴۰۴)  
**مخزن اصلی کار:** `Artexx` و سایر پروژه‌های `artexxpro`  
**شناسه چت (Cursor):** `70363c43-da39-4404-815d-f323d4901735`

---

## خلاصهٔ پرامپت‌ها (به ترتیب)

| # | پرامپت کاربر | نتیجهٔ کلی |
|---|--------------|------------|
| 1 | برنچ `main` پروژه **callcenter** را از برنچ‌های جدید آپدیت کن و push کن | merge به `main`، sync با origin |
| 2 | با پروژه **sanaradyab** هم همین کار را بکن | merge scaffold + version badge به `main` |
| 3 | برای بقیه پروژه‌های پوشه یک commit بفرست که hookها trigger شوند | commit خالی/ trigger در چند repo |
| 4 | همه پروژه‌ها sync شوند؛ چک کن شماره نسخه در صفحه هست یا نه | audit badge؛ sanaradyab اضافه شد |
| 5 | در **Minicrm** preset سامانه‌های پیامکی (کاوه‌نگار، فراپیامک، …) | `sms-providers.ts` + UI picker |
| 6 | یک تغییر کوچک در همه پروژه‌ها بده و push کن | README sync line / trigger commits |
| 7 | گزارش امروز | گزارش کارهای session |
| 8 | **Artexx:** badge نسخه با زیرنویس تداخل دارد | fix layout در `platform-version-badge.tsx` |
| 9 | در تنظیمات، `locale.direction` درست ترجمه نشده | **Chatbot-website:** restore فایل‌های i18n بعد از merge conflict |
| 10 | commit push | commit/push تغییرات Artexx + Chatbot |
| 11 | (اسکرین‌شات) چند روز است در «آخرین کارهای تبدیل» یک مورد «در انتظار بررسی» مانده | باگ: job بعد از approve کاندیدا `completed` نمی‌شد |
| 12 | Start multitasking | کار fix به subagent |
| 13 | postgres/docker دیدی — اینجا پروژه‌ای بالا نیست؛ چطور تست کردی؟ چطور Artexx را بالا بیاورم؟ | راهنمای اجرا؛ توضیح که Artexx اجرا نشده |
| 14 | pnpm/test کار نمی‌کند؛ deploy با hook روی سرور خارج از دسترس AI — قانون AI بساز | `.cursor/rules/readonly-workflow.mdc` |
| 15 | merge | push fix به `origin/cursor/artexx-platform-architecture-15b8` |
| 16 | push | push شاخه `fix/job-review-status` |
| 17 | checkout روی main نکرده بودی — با آخرین تغییرات merge کن بفرست | merge architecture → `main`، push `bb6285e` |

---

## چیزهایی که احتمالاً نمی‌دانستید (یا ارزش یادآوری دارد)

### ۱. دو Artexx متفاوت در یک repo

UI «**کنسول عملیات آرتکس**» (اسکرین‌شات شما) مربوط به **شاخه architecture** است (`apps/admin` + Fastify API روی **4000**)، نه `main` قدیمی که فقط `apps/web` + NestJS روی **3001** داشت. تا merge نهایی، deploy و باگ‌ها روی branch اشتباه دنبال می‌شد.

### ۲. «در انتظار بررسی» باگ دادهٔ دمو بود

`demo-landing.zip` در seed ساخته می‌شد، کاندیداها approve/reject می‌شدند، قالب و لندینگ هم publish می‌شد — اما **status خود job** از `review` به `completed` عوض نمی‌شد. fix: `syncJobReviewStatus` + `reconcileStuckReviewJobs` در startup API.

### ۳. postgres و پورت ۳۰۰۰ روی ماشین شما Artexx نیست

`docker ps` استک‌های **gps-platform** و **infrastructure** را نشان می‌داد. Artexx روی این دستگاه اجرا/تست end-to-end نشده؛ فقط تحلیل کد و push به GitHub.

### ۴. محیط dev این ماشین برای Artexx آماده نیست

- Node روی ماشین **v18** بود؛ پروژه **≥ 22** می‌خواهد  
- **pnpm** نصب نبود (دستور تست exit 127)  
- پورت **3000** و **5432** اشغال — برای Artexx باید admin روی **3003** یا compose را عوض کنید  

### ۵. deploy از دید AI قابل تأیید نیست

push به GitHub انجام می‌شود؛ **hook/post-receive** روی سرور maintainer deploy می‌کند. AI نمی‌تواند بگوید «الان روی production درست شد» مگر خودتان UI سرور را ببینید.

### ۶. i18n شکسته = اغلب merge conflict ناقص

در **Chatbot-website**، کلیدهایی مثل `locale.direction` در UI یعنی فایل `fa.json`/`en.json` در merge **بریده** شده بود (~۱۳۹ خط به‌جای ~۳۰۰). restore از commit قبل از merge + deep-merge کلیدهای جدید.

### ۷. تنظیمات Artexx admin ≠ Chatbot

صفحه «تنظیمات سامانه» در Artexx admin عمداً **کلید خام** (`locale.direction`) در جدول نشان می‌دهد — label فارسی در sidebar «پیش‌فرض‌های پلتفرم» است. اگر انتظار ترجمهٔ کلیدها در جدول دارید، باید UI جداگانه map شود.

### ۸. قانون AI برای صرفه‌جویی توکن

فایل: `/opt/new/artexxpro/.cursor/rules/readonly-workflow.mdc`  
پیش‌فرض: **بدون** `pnpm install/test`، **بدون** فرض docker = Artexx، **بدون** verify deploy — مگر شما صریح بگویید محیط آماده است.

### ۹. merge دو تاریخچهٔ متفاوت

`main` (NestJS MVP) و `fix/job-review-status` (architecture rewrite) **histories جدا** داشتند. merge به `main` با conflict در ۱۴ فایل؛ نسخه architecture برای فایل‌های conflict انتخاب شد → commit `bb6285e`.

---

## خروجی‌های فنی مهم

| موضوع | commit / محل |
|--------|----------------|
| Artexx version badge overlap | `7b408e2` |
| Chatbot i18n restore | `bb61d7e` |
| Minicrm SMS presets | merge در Minicrm-sms |
| Job review fix | `6286f22` → architecture branch |
| merge architecture → main | `bb6285e` on `origin/main` |

---

## اگر دوباره همان باگ «در انتظار بررسی» را دیدید

1. مطمئن شوید API از commit **`6286f22`** یا **`bb6285e`** deploy شده  
2. سرویس **api** restart شود (`reconcileStuckReviewJobs` در startup)  
3. در overview: `jobs.review` باید **0** و `demo-landing.zip` → **تکمیل‌شده**

---

## نکته برای چت‌های بعدی

- بگویید روی **کدام branch/deploy** کار می‌کنید (`main` vs architecture)  
- اگر فقط push می‌خواهید: «commit و push به main» صریح بگویید  
- برای تست محلی: اول تأیید Node 22 + pnpm؛ وگرنه AI فقط code review کند  

---

*آرشیو خودکار از session Cursor — workspace artexxpro*
