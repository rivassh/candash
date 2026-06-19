# TODO — باقی‌مانده از چت بایگانی استیج

**آخرین به‌روزرسانی:** ۲۰۲۶-۰۶-۲۰

## اولویت بالا

- [ ] **commit + push** تغییرات `/opt/stage-deployer` (~۳۹ فایل: `project_archive.py`, UI, YAMLها) — الان `main` حدود ۱۲ commit جلوتر از origin
- [ ] **modular-gps 502** — postgres/rabbitmq بالا؛ سرویس اپ احتمالاً down؛ deploy یا compose up
- [ ] **minicrm-sms** — خارج از keep list ولی `archived: false` و stack بالا؛ تصمیم: بایگانی مجدد یا اضافه به keep

## اولویت متوسط

- [ ] **verify همه URLهای keep** بعد از deploy نهایی
- [ ] **push مستندات llfs** — repo محلی `/opt/llfs` آماده؛ نیاز به SSH key برای `git@github.com:rivassh/llfs.git`
- [ ] **stage-deployer deploy رسمی** — restart انجام شد؛ commit/push + rule استیج بعد از merge

## انجام‌شده ✓

- [x] سیستم archive/unarchive + bulk
- [x] UI: گروه مهدی پیش‌فرض + کوکی + فیلتر بایگانی
- [x] انتقال ~۲۴ پوشه به `/opt/archived`
- [x] nginx regenerate + reload (رفع 502 داشبورد)
- [x] مستند usage + گزارش روزانه در `/opt/llfs/cursor/`
