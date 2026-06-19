# Follow-ups — اجرای کارهای باقی‌مانده هر چت

## اجرا

```bash
/opt/llfs/cursor/scripts/run-chat-followups.sh
```

### گزینه‌ها

| متغیر / آرگومان | معنی |
|-----------------|------|
| `2026-06-19-stage-archive` | فقط یک چت |
| `FORCE=1` | تکرار stepهای انجام‌شده |
| `SKIP_COMMIT=1` | بدون commit/push stage-deployer |
| `SKIP_LLFS_PUSH=1` | بدون push llfs |
| `MINICRM_ACTION=keep` | minicrm-sms را بایگانی نکن (پیش‌فرض: `archive`) |
| `--list` | لیست چت‌های ثبت‌شده |

## افزودن چت جدید

1. اسکریپت بساز: `follow-ups/YYYY-MM-DD-topic.sh`
2. یک خط به `manifest.tsv` اضافه کن:
   ```
   2026-06-21-my-topic	عنوان فارسی	2026-06-21-my-topic.sh	1
   ```
3. از `lib/followup-common.sh` استفاده کن (`run_step`, `verify_http`, …)

## state

هر step موفق → `follow-ups/.state/<chat_id>/<step>.done`  
اجرای مجدد فقط stepهای باقی‌مانده را انجام می‌دهد.

## چت فعلی: `2026-06-19-stage-archive`

1. commit + push `stage-deployer`
2. deploy `modular-gps`
3. minicrm-sms (archive یا keep)
4. restart deployer + nginx reload
5. verify URLهای keep
6. push `llfs`
