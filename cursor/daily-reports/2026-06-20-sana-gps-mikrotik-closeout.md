# Daily report � sana-gps / MikroTik / monitoring closeout

Date: 2026-06-20  
Project repo worked: `/opt/new/debops`  
Reporting repo: `git@github.com:rivassh/llfs.git`

## Technical execution recap

### 1) Recovery and validation path (from prior prompts in this chat)
- Investigated `502` behavior with direct-origin vs CDN checks.
- Accessed MikroTik live and identified wrong `dstnat` target for `94.182.193.115:80,443`.
- Corrected mapping back to `172.16.1.134`, added/verified `8089 -> 172.16.1.134:8081`.
- Confirmed both domains returned `HTTP 200` from multiple vantage points.

### 2) Management alignment
- Re-enabled/configured expected management path (`sysadmin`, SSH key-based path).
- Kept SSH restricted to private ranges and Winbox on configured port.

### 3) Monitoring escalation automation
- Added watchdog script with escalation chain:
  - outage detect -> SMS
  - unresolved after 60s -> voice call to `09107870867`
- Deployed on monitoring host with 1-minute cron schedule.
- Verified script execution/logging and monitoring->FreePBX SSH reachability.

## Documentation artifacts generated in `llfs`

- Prompt summary: `cursor/chats/2026-06-20-sana-gps-mikrotik-monitoring-prompts.md`
- Cost breakdown: `cursor/usage/2026-06-20-sana-gps-chat-cost-by-prompt.md`
- Open follow-ups: `cursor/todos/2026-06-20-sana-gps-chat-open.md`
- Updated rolling TODO: `cursor/TODO.md`

## Remaining open items

- Harden NAT rules to interface-based matching (`pppoe-out-Shatel-Radio`) instead of fixed public IP.
- Run post-change MikroTik backup export.
- Execute one controlled end-to-end alert drill (SMS then call).
- Restore Arvan API key scope for `sana-gps.ir` automation.

## Git status summary for reporting repo

- Branch: `main`
- Scope of this report push: closeout docs + TODO updates for sana-gps chat
