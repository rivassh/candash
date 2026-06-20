# خلاصه چت Cursor — modular-gps-tracking-platform

**تاریخ:** ۱۳–۱۴ ژوئن ۲۰۲۶  
**conversation:** `2d2eb80b-25a5-4c51-ab9d-cd73b8b5ccda`  
**HEAD نهایی:** `2d4e1db` (v0.8.4–0.8.8 + rebase روی origin)

---

## پرامپت‌ها → نتیجه

| # | پرامپت | نتیجه |
|---|--------|--------|
| 1 | تمام تست + HTML | Vitest 800/800؛ E2E 4/15؛ `docs/test-report-2026-06-13-full.html` |
| 2 | سناریوهای E2E | فهرست 9 spec / 15 test |
| 3 | playback در Traccar/legacy | تحلیل — polyline + timer + fitBounds |
| 4 | ارتقاء playback / «مستقیم» | `route-positions`، device-service، `useMapPlayback` — v0.8.2 |
| 5 | تم legacy: tb.png + آیکون APK | 14 PNG، picker، sheet — v0.8.3 — `1e6df0e` |
| 6 | commit push | fail شبکه |
| 7 | legacy/android فقط APK | `uiModePolicy` — v0.8.4 |
| 8 | FAB پشتیبانی squircle | v0.8.5 |
| 9 | روبان پایین APK | decompile → `map-chrome/` — v0.8.6 |
| 10 | زوم خیابانی | flyTo z=17 — v0.8.7 |
| 11 | playback اسنپ/تپسی | progressive route + pan — v0.8.8 |
| 12 | commit push / push | `fc62b30`→rebase→`2d4e1db` push OK؛ deploy fail 8081 |

---

## TODO باز (نگهداری: `docs/todo-list-1.md` §H + BACKLOG)

| ID | کار |
|----|-----|
| H1 | deploy prod: web + device-service + event-router |
| H2 | remote test env |
| H3 | E2E 11 failure |
| H4 | GitLab remote URL |
| H5 | ic_map_element (اختیاری) |

---

## یادگیری — چیزهایی که شاید نمی‌دانستید

### 1. decompile APK = منبع UI واقعی
آیکون‌های روبان از `main_device.xml` + `mipmap-xxhdpi` قابل استخراج‌اند — SVG دستی دقیق‌تر از PNG نیست.

### 2. `selectDeviceFromMarker` قبلاً zoom نمی‌کرد
کلیک marker فقط `presentDeviceOnMap(..., { zoom: false })` بود — رفتار Snapp نیاز به `zoom: true` + `IRAN_DEVICE_FOCUS_ZOOM`.

### 3. playback دو لایه polyline
اسنپ/تپسی: مسیر کامل خاکستری + segment طی‌شده رنگی — یک `L.polyline` کافی نیست؛ `setRouteProgress` لازم است.

### 4. commit playback = web + backend
`1e6df0e` شامل `device-service` و `event-router` (فیلتر GPS V) است — deploy فقط `web` کافی نیست.

### 5. rebase با origin = conflict changelog
origin `0.8.2/0.8.3` متفاوت بود؛ `SimpleMapFloatingControls` legacy branch + APK branch merge شد.

### 6. push ≠ deploy
`8081` روی لوکال = `metabase-adminer` — deploy prod باید روی سرور واقعی باشد.

### 7. Cursor cost در CSV = Included
اکثر eventها `Included` — مصرف بالا ≠ هزینه دلاری اضافه (پلن Pro/Ultra).

### 8. token در چت = ریسک امنیتی
`WorkosCursorSessionToken` را revoke کنید؛ از `.env.cursor.local` + hook استفاده کنید.

---

## مراجع

- Cost: `reports/2026-06-13-14-modular-gps-cursor-usage-cost.md`
- Git daily: `reports/2026-06-13-14-modular-gps-git-daily.md`
- Local copy: `.cursor/docs/2026-06-13-14-modular-gps-chat-usage-cost.md`
