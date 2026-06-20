# مخارج چت Cursor — modular-gps-tracking-platform

**conversation:** `6e006fe0-d241-4770-8a6e-5022856a79b2`  
**بازه:** ۱۹–۲۰ ژوئن ۲۰۲۶  
**ثبت:** ۲۰ ژوئن ۲۰۲۶

---

## وضعیت دریافت CSV رسمی Cursor

| منبع | بازه درخواستی | نتیجه |
|------|----------------|--------|
| `export-usage-events-csv` | `1781395200000` → `1781999999999` (۱۴–۲۰ ژوئن UTC) | **ناموفق** — HTTP 307 به WorkOS login |
| `ledger.jsonl` محلی | ۱۹–۲۰ ژوئن | **۰ event** (آخرین import CSV: ۱۷ ژوئن) |
| hook `prompt-log.jsonl` | این conversation | ۲ turn ثبت‌شده |

**علت:** `WorkosCursorSessionToken` در پرامپت منقضی/نامعتبر برای API بود؛ `CURSOR_SESSION_TOKEN` در `.env.cursor.local` روی این ماشین **تنظیم نشده** (`hasCredentials: false` در `SUMMARY.md`).

**برای دادهٔ رسمی بعدی:**

```bash
# token تازه از DevTools → Cookies → WorkosCursorSessionToken (فقط مقدار، بدون prefix)
# در .env.cursor.local — جزئیات: ai/cursor-usage/SECURITY-AND-SETUP.md

bash scripts/cursor-usage-track.sh --download-csv --days 7
bash scripts/cursor-usage-track.sh --sync
```

> **امنیت:** session token را در چت paste نکنید. token افشاشده در این session را در dashboard Cursor revoke/refresh کنید.

---

## جمع‌بندی مصرف (تخمین محلی)

| معیار | مقدار | منبع |
|--------|--------|------|
| حجم transcript | **651,351 B** | `agent-state.json` + hook |
| تخمین token کل (÷4) | **~162,838** | همان hook |
| تعداد پرامپت کاربر (واقعی) | **35** | parse transcript |
| پرامپت‌های پرمصرف (>10k tok تخمینی) | **#21, #24, #25, #29** | جدول پایین |

### hook — delta رسمی per turn (فقط ۲ stop ثبت‌شده)

| زمان (UTC) | deltaBytes | تخمین token |
|------------|------------|-------------|
| 2026-06-20T14:12:06 | 619,727 | 154,931 |
| 2026-06-20T18:19:28 | 31,624 | 7,906 |
| **جمع hook** | 651,351 | **162,837** |

---

## تفکیک گروهی (تخمین از transcript)

هر «turn» = از پرامپت کاربر تا پرامپت بعد (شامل پاسخ agent + tool results در transcript).  
این **≠** تعداد callهای billing Cursor (معمولاً چند event LLM per turn).

| گروه | پرامپت‌ها | تخمین token | سهم |
|------|-----------|-------------|-----|
| پورتال / لوگو / commit | 1–2 | 8,712 | 5% |
| لوکال dev vs prod / health | 3–6 | 4,786 | 3% |
| TODO پنل‌ها / staff / ui_references | 7–13 | 19,237 | 11% |
| Nexus داخلی / sync prod | 14–19 | 17,240 | 10% |
| اتوماسیون تأیید ثبت‌نام | 20–21 | 13,581 | 8% |
| Metabase WS monitoring | 22 | 6,227 | 4% |
| **خطای build registration-approval** | **23–25** | **62,675** | **37%** |
| IMEI + Enterprise Phase 1 | 26–31 | 28,956 | 17% |
| بستن چت / llfs / هزینه | 32–35 | 9,536 | 6% |

**نکته:** سه پرامپت تکراری خطای Docker build (#23–25) بیشترین سهم transcript را دارند — paste لاگ طولانی + چند دور fix.

---

## جدول per-prompt

| # | تخمین token | عنوان پرامپت |
|---|-------------|--------------|
| 1 | 6,219 | در سامانه sana-gps.ir قسمت بالایی حذف شود + لوگوی جدید |
| 2 | 2,492 | commit push |
| 3 | 446 | 8081 آزاد شد |
| 4 | 1,612 | لوکال dev — verify prod اشتباه است؟ |
| 5 | 1,498 | سرویس موقتاً در دسترس نیست |
| 6 | 1,228 | app.sana-gps.local روی لوکال |
| 7 | 3,699 | TODO پنل کاربران + تطبیق sanaradyab |
| 8 | 1,418 | ui_references submodule + متن RTL |
| 9 | 1,296 | منظورم جواب‌های این چت بود |
| 10 | 1,424 | زیرشماره برای agent بعدی |
| 11 | 1,335 | اولویت به لیست |
| 12 | 2,416 | آیتم ۶ لوکال — تکرار |
| 13 | 7,647 | آیتم ۶ لوکال — اجرا |
| 14 | 1,486 | خطای npm build (route-positions) |
| 15 | 2,621 | Nexus داخلی؟ |
| 16 | 8,849 | مستندات Nexus به پروژه |
| 17 | 1,449 | sync prod استثناپا |
| 18 | 1,553 | همه چیز از nexus؟ |
| 19 | 1,280 | خروجی setup-nexus-client |
| 20 | 2,527 | اتوماسیون تأیید کاربران جدید |
| 21 | 11,053 | پیاده‌سازی فقط super-admin |
| 22 | 6,227 | گزارش WS دستگاه (Metabase) |
| 23 | 1,885 | خطای registration-approval.ts (۱) |
| 24 | 28,882 | همان خطا (۲) |
| 25 | 31,907 | همان خطا (۳) |
| 26 | 1,149 | سیاست IMEI vs کد دستگاه |
| 27 | 3,454 | تکرار سیاست IMEI |
| 28 | 5,777 | شروع کن |
| 29 | 8,544 | Enterprise Phase 1 — branch جدید |
| 30 | 4,549 | fix migration 70 + گام بعد |
| 31 | 5,481 | commit push |
| 32 | 849 | لوکال — بدون .env.production |
| 33 | 990 | بستن چت + llfs |
| 34 | 6,841 | بستن چت (ادامه) |
| 35 | 855 | جزئیات مخارج + CSV |

---

## هزینه دلاری (CSV)

تا زمان تهیه این گزارش، **CSV رسمی دریافت نشد**. در importهای قبلی (۱۶–۱۷ ژوئن) ستون `Cost` برای اکثر eventها **`Included`** بود (پلن Pro/Ultra — بدون شارژ جدا per event).

پس از sync موفق، eventهای ۱۹–۲۰ ژوئن را در `ledger.jsonl` با `Cost` واقعی می‌توان دید.

---

## توصیه کاهش مصرف

1. **لاگ build/docker را خلاصه paste کنید** — #23–25 ≈ 37% transcript.
2. **پرامپت تکراری نزنید** — #12/#13 و #26/#27 و #33/#34.
3. **`.env.cursor.local` + hook** — sync خودکار بعد از هر turn بدون paste token در چت.
4. **چت‌های epic را ببندید** — Enterprise + Nexus + staff در یک thread ≈ 163k token تخمینی.

---

## مراجع

- Session summary: `chats/2026-06-20-modular-gps-cursor-session.md` (llfs)
- Git daily: `reports/2026-06-19-20-modular-gps-git-daily.md` (llfs)
- [README.md](../README.md) · [SECURITY-AND-SETUP.md](../SECURITY-AND-SETUP.md)
