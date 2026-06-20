# گزارش git روزانه — modular-gps-tracking-platform

**بازه:** ۱۳–۱۴ ژوئن ۲۰۲۶  
**branch:** `master`  
**HEAD:** `2d4e1db`

---

## Commits

| hash | تاریخ | پیام | SVC پیشنهادی |
|------|--------|------|--------------|
| `1e6df0e` | 2026-06-14 | feat(web): تم قدیمی + playback smart (0.8.3) | web, device-service, event-router, api-gateway |
| `2d4e1db` | 2026-06-14 | feat(web): روبان APK، zoom، playback UX (0.8.4–0.8.8) | web |

---

## Changelog (منتشرنشده تا deploy)

| نسخه | موضوع |
|------|--------|
| 0.8.3 | لوگو SR + 14 آیکون APK + playback backend |
| 0.8.4 | legacy/android فقط Capacitor |
| 0.8.5 | FAB پشتیبانی squircle |
| 0.8.6 | روبان پایین APK |
| 0.8.7 | زوم خیابانی flyTo |
| 0.8.8 | playback progressive + map follow |

---

## فایل‌های کلیدی

- `packages/common/src/route-positions.ts`
- `apps/web/src/hooks/useMapPlayback.ts`
- `apps/web/src/map/LeafletMapProvider.ts` (`setRouteProgress`)
- `apps/web/public/assets/map-chrome/*`
- `apps/web/src/themes/legacy/legacy-skin.css`
- `apps/web/src/utils/uiModePolicy.ts`

---

## وضعیت deploy

| مرحله | وضعیت |
|--------|--------|
| push origin | ✅ `2d4e1db` |
| rebase | ✅ conflict changelog + SimpleMapFloatingControls |
| prod deploy | ❌ لوکال — پورت 8081 (Adminer) |
| remote test | ❌ runner تنظیم نشده |

---

## Activity log (خلاصه)

| تاریخ | وضعیت | موضوع |
|------|--------|--------|
| 2026-06-13 | خارج از ساعات اداری | تست کامل + HTML |
| 2026-06-13 | خارج از ساعات اداری | تحلیل playback legacy |
| 2026-06-14 | ساعات اداری | playback backend/frontend v0.8.2–0.8.3 |
| 2026-06-14 | ساعات اداری | تم legacy + uiMode + FAB v0.8.4–0.8.5 |
| 2026-06-14 | ساعات اداری | map ribbon + zoom + playback UX v0.8.6–0.8.8 |
| 2026-06-14 | ساعات اداری | push OK؛ deploy ناموفق |
