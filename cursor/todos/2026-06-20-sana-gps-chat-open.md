# Open follow-ups � sana-gps / MikroTik chat

Date range: 2026-06-14 .. 2026-06-20  
Workspace: `/opt/new/debops`

## Pending technical items

- [ ] Harden MikroTik NAT rules so they do not depend on fixed public radio IP:
  - target rules: `80/443`, `8089`, `65001` families
  - approach: use `in-interface=pppoe-out-Shatel-Radio` and clean old `dst-address`-locked entries
- [ ] Run and archive a post-change RouterOS backup:
  - `./mikrotik/backup-mikrotik.sh`
- [ ] Execute controlled end-to-end alert drill:
  - make one short synthetic failure
  - verify order: SMS -> after 60s unresolved -> call `09107870867`
  - capture evidence (log lines + call attempt trace)
- [ ] Restore/enable Arvan API automation for `sana-gps.ir` account scope:
  - current key can access `artandev.ir`, `artexxpro.ir`, not `sana-gps.ir`
- [ ] Optional hardening:
  - run watchdog script under systemd timer instead of cron
  - add retry/backoff around FreePBX originate call

## Implemented in this chat (for context)

- Fixed MikroTik `dstnat` mapping that incorrectly sent `80/443` traffic to `192.168.160.10`.
- Restored `80/443` path to `172.16.1.134` and added/verified `8089 -> 172.16.1.134:8081`.
- Restored management alignment: `sysadmin` user + SSH key; Winbox on `5050`.
- Deployed monitoring watchdog (SMS then call escalation) on monitoring host.
