# مخارج Cursor — modular-gps (۱۳–۱۴ ژوئن ۲۰۲۶)

**conversation:** `2d2eb80b-25a5-4c51-ab9d-cd73b8b5ccda`  
**API بازه:** `startDate=1781395200000` & `endDate=1781999999999` (۱۴–۲۰ ژوئن UTC در URL)

---

## جمع‌بندی

| معیار | مقدار | توضیح |
|--------|--------|--------|
| transcript این چت | **342,174 B → ~85,500 token** | تخمین hook (÷4) |
| ledger ۱۳–۱۴ ژوئن (کل حساب) | **668M token / 665 event** | همهٔ workspaceها |
| ledger پنجره session | **~148M token / 141 event** | CHRONOLOGY #529–533 |
| Cost CSV | **Included ($0)** | import ۱۷ ژوئن |

---

## تفکیک روز (ledger — کل حساب)

| تاریخ | events | tokens |
|--------|-------:|-------:|
| 2026-06-13 | 303 | 343,853,411 |
| 2026-06-14 | 362 | 324,091,955 |

---

## مدل‌های پرمصرف (۱۳–۱۴)

| مدل | tokens |
|-----|-------:|
| composer-2.5-fast | 391,006,243 |
| gpt-5.3-codex | 94,467,566 |
| gpt-5.5-medium | 69,068,200 |

---

## per-prompt (تخمین transcript — این thread)

| گروه | پرامپت‌ها | موضوع |
|------|-----------|--------|
| A | 1–2 | تست + سناریو |
| B | 3–4 | playback تحلیل + پیاده‌سازی |
| C | 5–8 | تم legacy + uiMode + FAB |
| D | 9–11 | map ribbon + zoom + playback UX |
| E | 12 | push/deploy |

> CSV رسمی **Conversation ID ندارد** — برای هزینهٔ دقیق per-prompt فقط تخمین transcript.

---

## توصیه

1. `.env.cursor.local` + `cursor-usage-track.sh` — بدون paste token در چت  
2. token افشاشده در پرامپت بستن چت را **revoke** کنید  
3. چت epic را ببندید — context طولانی = cache read بالا در ledger

---

## مراجع

- Session: `chats/2026-06-13-14-modular-gps-cursor-session.md`
- Git: `reports/2026-06-13-14-modular-gps-git-daily.md`
