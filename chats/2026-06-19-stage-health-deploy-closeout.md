# Stage health & deploy chat closeout — 2026-06-19

**Workspace:** `/opt` (stage-deployer + پروژه‌های stage)  
**Transcript:** `a93bbde8-393a-443d-a2a2-379651d03f35`  
**Scope:** سلامت همه پروژه‌های stage، دیپلوی مجدد پروژه‌های مشکل‌دار، Nexus، GitLab CI، closeout + llfs

## Prompt Summary

| # | Theme | Result |
|---:|---|---|
| 1 | چک سلامت همه پروژه‌ها | گزارش از API + HTTP: 24 سالم، 3 ناقص، 3 متوقف، 8 دیپلوی‌نشده |
| 2 | ادامه دیپلوی (sanaradyab, mix-proj, …) | sanaradyab/mix-proj با docker start؛ subagent قطع شد |
| 3 | Nexus برای buildهای اولیه | راهنمای کامل (Liara فعلی؛ Nexus داخلی ثبت نشده) |
| 4 | چرا `/sanaradyab-ws/` → 502 | nginx درست؛ upstream پورت 18059 / WS container |
| 5 | آیا هر بار باید AI صدا بزنیم؟ | خیر — webhook + health cron + runbook |
| 6 | GitLab CI قرمز (runner/k8s) | CI ≠ stage deploy؛ ساده‌سازی yaml + webhook |
| 7 | closeout: TODO، هزینه، llfs، یادگیری | این سند + push به `rivassh/llfs` |

## What To Learn From This Chat

چیزهایی که در این چت مشخص شد **قبلاً در عمل روشن نبود** یا اشتباه فرض شده بود:

1. **استیج با webhook + stage-deployer است، نه GitLab Runner/K8s.** پر کردن `.gitlab-ci.yml` با runner/k8s بدون زیرساخت → pipeline قرمز؛ اپ stage ممکن است جداگانه سالم باشد.

2. **`docker-compose.stage.yml` override است، نه جایگزین.** bootstrap قبلاً فقط stage file را می‌گرفت → خطای websocket «no image nor build». fix در `1212f1c` (هنوز push نشده).

3. **push به ریپوی `stage-deployer` دیپلوی خودکار نمی‌زند.** خودش slug ثبت‌شده نیست؛ webhook برای پروژه‌های دیگر است. اسکریپت‌های mount‌شده با `git pull` فوری اعمال می‌شوند.

4. **502 روی path جدا (مثل `-ws`) یعنی upstream آن پورت down است** — نه لزوماً کل پروژه. API می‌تواند 200 بدهد و WS 502.

5. **نگهداری بدون AI:** `system_health.py`, داشبورد `/stage/`, `deploy-project.sh` — webhook token باید درست باشد (۱۹ invalid در لاگ).

6. **دیپلوی پس‌زمینه همیشه جاری نیست.** چند shell task تمام شد؛ یک subagent قطع شد — بعد از closeout کار باز در TODO است.

7. **هزینه Cursor در export اغلب `Included` است** — توکن زیاد ≠ دلار مستقیم در CSV؛ cache read حجم اصلی است.

8. **هرگز cookie/session Cursor را در چت نفرست** — فقط برای export موقت؛ بعد logout/login.

## Security

- Session token در پرامپت closeout بود — در llfs commit نشده. چرخش session توصیه می‌شود.

## Links

- TODO: [cursor/todos/2026-06-19-stage-health-deploy-chat-open.md](../cursor/todos/2026-06-19-stage-health-deploy-chat-open.md)
- Cost: [cursor/usage/stage-health-deploy-2026-06-18_2026-06-20-cost-by-prompt.md](../cursor/usage/stage-health-deploy-2026-06-18_2026-06-20-cost-by-prompt.md)
- Git daily: [reports/2026-06-19-stage-health-deploy-git-daily.md](../reports/2026-06-19-stage-health-deploy-git-daily.md)
