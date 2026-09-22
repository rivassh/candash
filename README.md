# TalentMatch

سامانه هوشمند تحلیل و مدیریت استعداد (Client-based).

> این فاز بر پایه Mock و فرمول‌های قطعی ساخته شده و هیچ اتصالی به API هوش مصنوعی واقعی ندارد.

## معماری

- **api** — Laravel 13 + PHP 8.4 — موتور تطبیق، REST API، احراز هویت Sanctum
- **frontend** — Nuxt 3 + Vue 3 + Tailwind — رابط فارسی RTL
- **db** — PostgreSQL 16 + pgvector
- **redis** — صف و کش
- **mock-jobsource** — سرویس Node.js برای شبیه‌سازی منبع شغلی

## شروع سریع

```bash
make init DOMAINS=""  # یا make up
make migrate
make seed
```

سپس:
- فرانت‌اند: http://localhost:3080
- API: http://localhost:8082/api
- Mock JobSource: http://localhost:4000

**اطلاعات ورود:**
- `admin@talentmatch.local` / `admin123` (ادمین)
- `hr@talentmatch.local` / `hr123456` (کارشناس HR)

## ساختار پروژه

```
.
├── api/                # Laravel application
│   ├── app/
│   │   ├── Contracts/      # اینترفیس‌های دامنه (JobSource, Resume, Enrichment)
│   │   ├── Services/       # پیاده‌سازی (Mock, External)
│   │   │   ├── Matching/   # موتور تطبیق فرمولی
│   │   │   ├── JobSource/
│   │   │   ├── Resume/
│   │   │   └── Enrichment/
│   │   ├── Models/         # Eloquent models
│   │   ├── DTOs/
│   │   ├── Enums/
│   │   └── Http/Controllers/Api/
│   ├── database/migrations/  # مایگریشن‌ها
│   ├── database/seeders/     # سیدرها
│   └── routes/api.php
├── frontend/           # Nuxt 3 (RTL + Tailwind)
│   ├── pages/
│   │   ├── index.vue         # ورود
│   │   ├── dashboard.vue     # داشبورد
│   │   ├── positions/        # مدیریت موقعیت‌ها
│   │   ├── candidates/       # کاندیداها + پروفایل
│   │   ├── matches.vue       # موتور تطبیق
│   │   ├── skills.vue        # دیکشنری مهارت‌ها
│   │   └── audit.vue         # گزارش تغییرات
│   ├── layouts/default.vue
│   ├── stores/auth.ts
│   └── composables/useApi.ts
├── contracts/
│   └── jobsource.yaml   # قرارداد OpenAPI منبع شغلی
├── mock-jobsource/      # شبیه‌ساز Node.js
└── db/init/             # اسکریپت‌های pgvector
```

## موتور تطبیق (Matching Engine)

فرمول قطعی و قابل توضیح:

```
total = 0.40 × required_skills
      + 0.20 × experience
      + 0.15 × seniority
      + 0.10 × education
      + 0.10 × preferred_skills
      + 0.05 × stability
```

پیاده‌سازی: `api/app/Services/Matching/MatchingService.php`

خروجی هر تطبیق:
- `total_score` (۰ تا ۱۰۰)
- `breakdown` (jsonb با ریز نمرات)
- `strengths` (آرایه فارسی از نقاط قوت)
- `gaps` (آرایه فارسی از کمبودها)

## لایه Mock

تمام سرویس‌های خارجی فقط از طریق اینترفیس‌ها قابل استفاده‌اند:
- `App\Contracts\JobSource\JobSourceInterface` → `MockDriver` / `ExternalApiDriver`
- `App\Contracts\Resume\ResumeExtractorInterface` → `MockResumeExtractor`
- `App\Contracts\Enrichment\EnrichmentInterface` → `MockEnrichmentService`

تغییر درایور: `JOBSOURCE_DRIVER=external` در `.env`

## دستورات Make

| دستور | توضیح |
|------|------|
 `make up` | ساخت و راه‌اندازی همه سرویس‌ها |
| `make down` | توقف و حذف کانتینرها |
| `make migrate` | اجرای مایگریشن‌ها |
| `make seed` | اجرای سیدرها |
| `make fresh` | مایگریشن از صفر + سید |
| `make test` | اجرای تست‌های PHPUnit |
| `make bash-api` | ورود به شل api |

## تست

```bash
make test
```

تست نمونه موتور تطبیق در `api/tests/Unit/MatchingServiceTest.php`.
