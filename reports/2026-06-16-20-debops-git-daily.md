# گزارش روزانه Git + فعالیت — debops

**مخزن:** `git@github.com:hmdshariati/devops` (محلی: `/opt/new/debops`)  
**بازه:** ۱۶–۲۰ ژوئن ۲۰۲۶ (≈ ۲۶–۳۰ خرداد ۱۴۰۵)  
**منبع:** `git log` + پرامپت‌های چت `7e50f082-edea-4298-90dd-1f9331413fed` + diff uncommitted

---

## خلاصه commitهای Git (push شده)

| تاریخ | SHA | پیام commit | حجم |
|--------|-----|-------------|-----|
| 2026-06-18 | `383f570` | Initial devops: cursor prompt metrics and stage health | bootstrap |
| 2026-06-18 | `e876ce5` | Fix workspace hook installer and ignore archived /opt root files | کوچک |
| 2026-06-19 | `ad9c15f` | Add Cursor usage tracking workflow | cursor-usage |
| 2026-06-20 | `e8d3564` | a log changes | ~122 فایل (+8580/-1224) |

**۱۵ ژوئن (مرجع):** Issabel LXC، MikroTik backup، reorg repo، handover docs.

---

## ۱۶ ژوئن — ۲۶ خرداد

### Git
commit جدید در این روز روی `main` نیست.

### فعالیت

| وضعیت زمانی | موضوع | توضیح فنی |
|-------------|--------|-----------|
| ساعات اداری | Stage AD login | LDAP برای nextcloud + devops_portal |
| ساعات اداری | Implement plan AD | Stage 150؛ blocker bind/StartTLS |
| ساعات اداری | guarantee 502 | Arvan origin 65001 timeout؛ 31880 OK |

---

## ۱۷ ژوئن — ۲۷ خرداد

### Git
commit push نشده.

### فعالیت

| وضعیت زمانی | موضوع | توضیح فنی |
|-------------|--------|-----------|
| ساعات اداری | AD + nextcloud-svc | راهنمای AD؛ تست Linux |
| ساعات اداری | analytics-all | 10882 events؛ Metabase دستی |
| ساعات اداری | minicrm-sms 502 | stack down؛ deploy + HTTP 200 |
| ساعات اداری | stage deploy guard | deploy-path-safe + hooks روی 150 |
| ساعات اداری | support_analytics | Access denied localhost — pending |
| ساعات اداری | Adminer Stage DB | publish/socat — pending |
| ساعات اداری | activity-log rule | activity-log-auto.mdc |

---

## ۱۸ ژوئن — ۲۸ خرداد

### Git

```
383f570  Initial devops: cursor prompt metrics and stage health
e876ce5  Fix workspace hook installer and ignore archived /opt root files
```

### فعالیت
راه‌اندازی Cursor usage tracking و stage health در debops.

---

## ۱۹ ژوئن — ۲۹ خرداد

### Git

```
ad9c15f  Add Cursor usage tracking workflow
```

### فعالیت

| وضعیت زمانی | موضوع | توضیح فنی |
|-------------|--------|-----------|
| خارج از ساعات اداری | Nexus خارج شرکت | VPN + setup-nexus-client |
| خارج از ساعات اداری | ai-workflow-playbook | propagate cursor usage |
| خارج از ساعات اداری | commit push | push به remote |
| خارج از ساعات اداری | audit بک‌آپ | GPS DB dump نداریم |

---

## ۲۰ ژوئن — ۳۰ خرداد

### Git

```
e8d3564  a log changes
```

**محتوای اصلی:** support-analytics SQL، stage-deployer، VoIP 9902، monitoring Grafana، MikroTik، docs.

### فعالیت

| وضعیت زمانی | موضوع | توضیح فنی |
|-------------|--------|-----------|
| خارج از ساعات اداری | دامنه + خاموش آروان | inventory SSL/cutover |
| خارج از ساعات اداری | VPS edge فاز ۱ | LE + WireGuard |
| خارج از ساعات اداری | ESXi / Proxmox / Foreman | todo VM Factory |
| خارج از ساعات اداری | stage self-heal | cron watchdog + auto-heal + deploy lock |
| خارج از ساعات اداری | fix artexx health URL | host_port + prefix |
| خارج از ساعات اداری | llfs summary | chats/2026-06-20-debops-cursor-session.md |

### uncommitted (بعد از e8d3564)

- Makefile: stage-auto-heal، zabbix-stage-projects
- backup: sana-gps-postgres-heavy.sh (NEW)
- stage-deployer: deploy lock، infra-watchdog، auto-heal sync

**پیشنهاد commit:** `stage guard v2 + GPS postgres backup scripts`

---

## وضعیت branch (۲۰ ژوئن)

```
main...origin/main
 M 14 files
 ?? 3 files
```

---

## TODO باز

AD bind، analytics localhost، Adminer Stage، GPS backup deploy، Proxmox/Foreman، Arvan cutover.

جزئیات: [../chats/2026-06-20-debops-cursor-session.md](../chats/2026-06-20-debops-cursor-session.md)

---

```bash
cd /opt/new/debops && git log --oneline --since=2026-06-16 --until=2026-06-21
git diff --stat HEAD
```
