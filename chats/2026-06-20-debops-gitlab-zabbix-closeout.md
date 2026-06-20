# Debops closeout — GitLab reorg + printer/Zabbix

**Workspace:** `/opt/new/debops`  
**Date:** 2026-06-20  
**Cost file:** `cursor/usage/debops-gitlab-zabbix-2026-06-14_2026-06-20-cost-by-prompt.md`

## Done in this chat

- GitLab project inventory collected from self-hosted instance and grouped by domain.
- New GitLab groups created: `gps`, `maps`, `erp`, `voip`, `ai`.
- Project transfer/restructure executed; local `debops` remote updated to `infra/devops`.
- Printer `172.16.1.141` outage confirmed from client + monitoring host (ICMP/ARP unreachable).
- Zabbix path investigated: problem existed, SMS delivery failed due to JSON/newline escaping bug.
- `scripts/zabbix-novin-sms.sh` fixed for newline-safe JSON payload, deployed to Zabbix server container.
- End-to-end retest run by closing/retriggering printer problem event; alert delivery succeeded.
- Activity-log table produced from chat history (`2026-06-10`, `2026-06-14`, `2026-06-16`).

## What to learn from this chat

1. **Zabbix can have an active problem without visible SMS outcome**; always check `problem.get` plus `alert.get` status/error.
2. **Line breaks in alert payloads break naive JSON scripts**; alert scripts must escape `\n` and `\r`.
3. **GitLab project transfer can fail on container registry tags**; registry cleanup is required before move.
4. **Namespace migration affects remotes**; local `origin` should be updated explicitly after project transfer.
5. **For postmortems, split layers**: network reachability, monitoring trigger state, then notification transport.

## Open follow-ups kept in TODO

- Transfer 4 blocked GitLab projects after cleaning container registry tags.
- Resolve printer network/power path for `172.16.1.141` (still unreachable).

## Security note

- A Cursor web session cookie was included in prompt text; rotate/revoke session and avoid storing cookies in repo files.
