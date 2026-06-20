# بستن چت — llfs (کم‌توکن)

<div dir="rtl">

## یک خط در Cursor

```
llfs close TOPIC
```

مثال: `llfs close stage-webhook` · `llfs close debops`

Agent **پرامپت بلند closeout را تکرار نمی‌کند.**

---

## جریان

| مرحله | چه کسی | کار |
|--------|--------|-----|
| 1 | Agent | `bash /opt/llfs/cursor/scripts/close-chat.sh TOPIC` |
| 2 | Agent | فقط بخش **Learnings** (≤۵ bullet) و TODO باز در فایل‌های تولید‌شده |
| 3 | Agent | `bash /opt/llfs/cursor/scripts/close-chat.sh TOPIC --push` |

---

## اسکریپت (بدون LLM)

```bash
# مسیر پیش‌فرض روی stage VM
bash /opt/llfs/cursor/scripts/close-chat.sh TOPIC \
  [--start YYYY-MM-DD] [--end YYYY-MM-DD] \
  [--workspace /opt/stage-deployer] \
  [--repos /opt/stage-deployer/devops] \
  [--skip-csv] [--force]

bash /opt/llfs/cursor/scripts/close-chat.sh TOPIC --push
```

| خروجی | مسیر |
|--------|------|
| Closeout | `chats/YYYY-MM-DD-TOPIC-closeout.md` |
| Git daily | `reports/YYYY-MM-DD-TOPIC-git-daily.md` |
| Cost stub + CSV | `cursor/usage/` |
| TODO این چت | `cursor/todos/YYYY-MM-DD-TOPIC-chat-open.md` |

---

## Cookie Cursor (هرگز در چت نفرست)

```bash
mkdir -p ~/.config
cat >> ~/.config/cursor-usage.env <<'EOF'
CURSOR_USAGE_COOKIE='WorkosCursorSessionToken=...'
EOF
chmod 600 ~/.config/cursor-usage.env
```

تست:

```bash
bash /opt/llfs/cursor/scripts/fetch-cursor-usage-csv.sh 2026-06-14 2026-06-20 | head
```

---

## follow-up عملیاتی (جدا)

کارهای deploy/verify باقی‌مانده:

```bash
bash /opt/llfs/cursor/scripts/run-chat-followups.sh
```

---

## Rule

`.cursor/rules/llfs-close-chat.mdc` — در `/opt` و کپی در `cursor/rules/` برای workspaceهای دیگر.

</div>
