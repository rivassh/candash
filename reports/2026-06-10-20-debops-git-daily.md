# Git daily — debops (2026-06-10 … 2026-06-20)

**Repo:** `/opt/new/debops`  
**Focus:** GitLab reorg + Zabbix printer alert incident + chat closeout docs

## Commits by day

### 2026-06-10
- `dc70517` Add internal monitoring setup for MikroTik and project servers.
- `bf7a121` Fix internal Prometheus/Alertmanager URLs with route prefix.
- `5d1583c` Document MikroTik bandwidth apply plan with rollback steps.
- `2b795fb` Add MikroTik backup and post-check scripts for monitoring rollout.
- `2cf334c` Add office monitoring rollout, bandwidth reporting, and PC inventory.

### 2026-06-11
- `e979982` Wire Zabbix alerts to Novin sms-service for SMS notifications.

### 2026-06-13
- `4ea8556` Add artandev DNS/NAT automation and deployment scripts for infra services.
- `bef6d04` Document CRM fix and add tooling for Arvan, Nexus, and Nextcloud apps.
- `329afd0` Automate Nextcloud auth checks and stage project health alerts.
- `bce0f2e` Add OpenVPN monitoring and expand infra health-check tooling.

### 2026-06-15
- `747d56c` Reorganize network docs and add MikroTik config as a submodule.
- `b466e4c` Add VoIP support queue tooling, OpenVPN helpers, and monitoring scripts.
- `234ce94` Add Persian handover docs for credentials delivery without committing secrets.
- `7517d52` Add MikroTik 115343 post-restore scripts and passwordless backup defaults.
- `ba0724a` Reorganize repo into deeper subfolders for easier navigation in chat.
- `da9f083` some changes.
- `d168831` mikrotik backups.
- `5fe23c9` Add Issabel LXC Asiatech VoIP stack and MikroTik NAT for 172.16.1.116.

### 2026-06-18
- `383f570` Initial devops: cursor prompt metrics and stage health.
- `e876ce5` Fix workspace hook installer and ignore archived `/opt` root files.

### 2026-06-19
- `ad9c15f` Add Cursor usage tracking workflow.

### 2026-06-20
- `e8d3564` a log changes.

## Work executed from this chat (not all committed in debops)

- GitLab namespaces created and projects transferred by API (some blocked by registry tags).
- Zabbix Novin SMS alertscript fixed for newline-safe JSON payload and redeployed.
- End-to-end printer alert retrigger test completed with successful SMS send status.
- Activity log and per-prompt usage documentation generated.
