# llfs — Learning & session logs

Personal archive of Cursor chat summaries, prompts, lessons learned, and daily git reports.

## Structure

- `chats/` — session summaries (markdown)
- `reports/` — daily git + activity + Cursor usage/cost reports
- `cursor/` — stage VM docs, follow-up scripts, usage CSV
- `cursor/reminders/` — یادآوری‌های دائمی per-person (sync با git)
- root — standalone learnings / cross-cutting notes

## یادآوری محمد مهدی سنایی (هر دستگاه)

لیست مشترک: [`cursor/reminders/mohammad-mahdi-senaie-todos.md`](cursor/reminders/mohammad-mahdi-senaie-todos.md)

Rule: [`cursor/rules/mohammad-mahdi-senaie-reminder.mdc`](cursor/rules/mohammad-mahdi-senaie-reminder.mdc) — در هر چت می‌پرسد «کی کار می‌کند» و موارد باز را یادآوری می‌کند.

نصب rule در پروژه یا **همه چت‌ها** (user rule):

```bash
./ai-workflow-playbook/scripts/install-senaie-reminder.sh --user   # همه پروژه‌ها
./ai-workflow-playbook/scripts/install-senaie-reminder.sh /path/to/project
```

## Follow-ups (اجرای کارهای باقی‌مانده چت)

```bash
LLFS_DIR=/opt/llfs bash /opt/llfs/cursor/scripts/bootstrap-followups.sh
```

یا: `bash /opt/llfs/cursor/scripts/run-chat-followups.sh`

## بستن چت (کم‌توکن)

```
llfs close TOPIC
```

Playbook: [`cursor/CLOSE-CHAT.md`](cursor/CLOSE-CHAT.md) · Rule: [`cursor/rules/llfs-close-chat.mdc`](cursor/rules/llfs-close-chat.mdc)

```bash
bash /opt/llfs/cursor/scripts/close-chat.sh TOPIC
# agent: Learnings + TODO
bash /opt/llfs/cursor/scripts/close-chat.sh TOPIC --push
```

CSV: `~/.config/cursor-usage.env` + `fetch-cursor-usage-csv.sh` — **cookie در چت نفرست.**

## فهرست — Passbolt / debops (۱۹ ژوئن ۲۰۲۶)

| نوع | فایل |
|-----|------|
| یادگیری‌ها | [chats/2026-06-19-passbolt-closeout.md](chats/2026-06-19-passbolt-closeout.md) |
| پرامپت‌ها | [cursor/chats/2026-06-19-passbolt-prompts.md](cursor/chats/2026-06-19-passbolt-prompts.md) |
| هزینه Cursor | [cursor/usage/passbolt-chat-2026-06-19-cost.md](cursor/usage/passbolt-chat-2026-06-19-cost.md) |
| گزارش روزانه Git | [reports/2026-06-19-debops-passbolt-daily.md](reports/2026-06-19-debops-passbolt-daily.md) |
| TODO باز | [cursor/TODO.md](cursor/TODO.md) |

## فهرست — stage-deployer (۱۹ ژوئن ۲۰۲۶)

| نوع | فایل |
|-----|------|
| یادگیری‌ها | [2026-06-19-stage-deployer-chat-learnings.md](2026-06-19-stage-deployer-chat-learnings.md) |
| گزارش روزانه Git | [reports/2026-06-19-daily-git-report.md](reports/2026-06-19-daily-git-report.md) |
| پرامپت‌ها + TODO | [cursor/chats/2026-06-19-stage-archive-prompts.md](cursor/chats/2026-06-19-stage-archive-prompts.md) |
| Usage ۱۴–۲۰ ژوئن | [cursor/usage/summary-2026-06-14_2026-06-20.md](cursor/usage/summary-2026-06-14_2026-06-20.md) |

## سایر گزارش‌ها

| تاریخ | فایل |
|--------|------|
| debops closeout | [chats/2026-06-20-debops-closeout.md](chats/2026-06-20-debops-closeout.md) |
| debops daily 2026-06-20 | [reports/2026-06-20-debops-daily.md](reports/2026-06-20-debops-daily.md) |
| debops cost by prompt | [cursor/usage/debops-2026-06-14_2026-06-20-cost-by-prompt.md](cursor/usage/debops-2026-06-14_2026-06-20-cost-by-prompt.md) |
| debops git | [reports/2026-06-16-20-debops-git-daily.md](reports/2026-06-16-20-debops-git-daily.md) |
| modular-gps git | [reports/2026-06-19-20-modular-gps-git-daily.md](reports/2026-06-19-20-modular-gps-git-daily.md) |
| debops Cursor cost | [reports/2026-06-20-debops-cursor-usage-cost.md](reports/2026-06-20-debops-cursor-usage-cost.md) |
| modular-gps Cursor cost | [reports/2026-06-20-modular-gps-cursor-cost.md](reports/2026-06-20-modular-gps-cursor-cost.md) |
| **stage-deployer UI chat (۲۰ ژوئن)** | [chats/2026-06-20-stage-deployer-ui-cursor-session.md](chats/2026-06-20-stage-deployer-ui-cursor-session.md) |
| **stage health/deploy (۱۹ ژوئن)** | [chats/2026-06-19-stage-health-deploy-closeout.md](chats/2026-06-19-stage-health-deploy-closeout.md) |
| TODO باز ۱۹ ژوئن | [cursor/todos/2026-06-19-stage-health-deploy-chat-open.md](cursor/todos/2026-06-19-stage-health-deploy-chat-open.md) |
| Cost ۱۹ ژوئن stage health | [cursor/usage/stage-health-deploy-2026-06-18_2026-06-20-cost-by-prompt.md](cursor/usage/stage-health-deploy-2026-06-18_2026-06-20-cost-by-prompt.md) |
| Git daily ۱۹ ژوئن stage health | [reports/2026-06-19-stage-health-deploy-git-daily.md](reports/2026-06-19-stage-health-deploy-git-daily.md) |
| **opt/devops chat (۱۷–۲۰ ژوئن)** | [cursor/chats/2026-06-18-opt-stage-deployer-devops-session.md](cursor/chats/2026-06-18-opt-stage-deployer-devops-session.md) |
| opt/devops learnings | [chats/2026-06-20-opt-stage-deployer-chat-learnings.md](chats/2026-06-20-opt-stage-deployer-chat-learnings.md) |
| opt/devops git daily | [reports/2026-06-18-20-opt-devops-git-daily.md](reports/2026-06-18-20-opt-devops-git-daily.md) |
| opt/devops cost | [cursor/usage/2026-06-18-opt-devops-cost-by-prompt.md](cursor/usage/2026-06-18-opt-devops-cost-by-prompt.md) |
| opt/devops TODO | [cursor/todos/2026-06-18-opt-devops-chat-open.md](cursor/todos/2026-06-18-opt-devops-chat-open.md) |
| stage-deployer UI cost | [reports/2026-06-20-stage-deployer-ui-cursor-cost.md](reports/2026-06-20-stage-deployer-ui-cursor-cost.md) |
| stage-deployer UI git | [reports/2026-06-18-20-stage-deployer-ui-git-daily.md](reports/2026-06-18-20-stage-deployer-ui-git-daily.md) |
| stage-deployer UI TODO | [cursor/todos/stage-deployer-ui-chat-open.md](cursor/todos/stage-deployer-ui-chat-open.md) |
| **llfs close automation (۲۰ ژوئن)** | [cursor/CLOSE-CHAT.md](cursor/CLOSE-CHAT.md) · [chats/2026-06-20-stage-subdomain-github-webhook-closeout.md](chats/2026-06-20-stage-subdomain-github-webhook-closeout.md) |
| infra audit cost | [cursor/usage/debops-infra-audit-chat-2026-06-19-20-cost.md](cursor/usage/debops-infra-audit-chat-2026-06-19-20-cost.md) |
| infra audit git daily | [reports/2026-06-20-debops-infra-audit-daily.md](reports/2026-06-20-debops-infra-audit-daily.md) |
| infra audit TODO | [cursor/todos/infra-audit-chat-open.md](cursor/todos/infra-audit-chat-open.md) |
| **Sana GPS NAT closeout (۲۱ ژوئن)** | [chats/2026-06-21-debops-sana-gps-nat-closeout.md](chats/2026-06-21-debops-sana-gps-nat-closeout.md) |
| Sana GPS NAT prompts | [cursor/chats/2026-06-21-debops-sana-gps-nat-prompts.md](cursor/chats/2026-06-21-debops-sana-gps-nat-prompts.md) |
| Sana GPS NAT cost | [cursor/usage/debops-sana-gps-nat-2026-06-14_2026-06-21-cost.md](cursor/usage/debops-sana-gps-nat-2026-06-14_2026-06-21-cost.md) |
| Sana GPS NAT git daily | [reports/2026-06-21-debops-sana-gps-nat-git-daily.md](reports/2026-06-21-debops-sana-gps-nat-git-daily.md) |
| Sana GPS NAT TODO | [cursor/todos/2026-06-21-debops-sana-gps-nat-chat-open.md](cursor/todos/2026-06-21-debops-sana-gps-nat-chat-open.md) |
