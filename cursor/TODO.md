# TODO — follow-ups left from Cursor chats

**Last updated:** 2026-06-11 (Metabase/NC/Stage + Nexus/OpenVPN) · 2026-06-16 (VoIP) · 2026-06-20 (stage-deployer + GPS/activity closeout) · 2026-06-21 (Health dashboard + Activity log + project activity log)

See also: [todos/2026-06-21-debops-project-activity-log-chat-open.md](todos/2026-06-21-debops-project-activity-log-chat-open.md) · [todos/2026-06-21-debops-activity-log-chat-open.md](todos/2026-06-21-debops-activity-log-chat-open.md) · [todos/2026-06-21-debops-health-dashboard-chat-open.md](todos/2026-06-21-debops-health-dashboard-chat-open.md) · [todos/2026-06-20-gps-activity-closeout-chat-open.md](todos/2026-06-20-gps-activity-closeout-chat-open.md) · [todos/2026-06-20-debops-gitlab-zabbix-chat-open.md](todos/2026-06-20-debops-gitlab-zabbix-chat-open.md) · [todos/debops-metabase-nextcloud-stage-chat-open.md](todos/debops-metabase-nextcloud-stage-chat-open.md) · [todos/2026-06-10-11-nexus-openvpn-chat-open.md](todos/2026-06-10-11-nexus-openvpn-chat-open.md) · [chats/2026-06-10-debops-metabase-nextcloud-closeout.md](../chats/2026-06-10-debops-metabase-nextcloud-closeout.md)

## Activity log from transcript 1241c6d0 (from 2026-06-21 closeout)

- [ ] `monitoring-disk-100gb` — ESXi attach + LVM extend on `172.16.1.164` (disk not visible; no LVM).
- [ ] `zoiper-305-split-tunnel` — client split tunnel + REGISTER; password `654JY&`.
- [ ] `support-queue-register-302-305` — queue operators must register Zoiper/VPN.
- [ ] `activity-log-full-rows-restore` — optional restore 18-row log superseded by `e8d3564` summary.
- [ ] `activity-log-timestamp-crosscheck` — inferred dates (JSONL lacks message timestamps).
- [ ] `activity-log-usage-cost-exact` — import Cursor Usage CSV for transcript `1241c6d0`.

Detail: [todos/2026-06-21-debops-activity-log-chat-open.md](todos/2026-06-21-debops-activity-log-chat-open.md)

## Debops project activity log (from 2026-06-21 closeout)

- [ ] `arvan-sanaradyab-panel-fix` — set/purge `sanaradyab.ir` origin in Arvan panel; current API key cannot access that domain.
- [ ] `ad-dc-172-16-1-246-recovery` — restore reachability to Windows AD/LDAP/DC at `172.16.1.246`.
- [ ] `nextcloud-ldap-watchdog-green` — re-run `occ ldap:test-config s01` and monitoring watchdog after AD is reachable.
- [ ] `cursor-usage-exact-project-activity-log` — import fresh Cursor Usage CSV and calculate exact cost for this closeout.

Detail: [todos/2026-06-21-debops-project-activity-log-chat-open.md](todos/2026-06-21-debops-project-activity-log-chat-open.md)

## Health dashboard / Monitoring portal (from 2026-06-21 chat)

- [ ] `health-dashboard-deploy` — deploy `monitoring/portal/health.html` to monitoring and smoke-test `http://172.16.1.164:8090/health.html`.
- [ ] `debops-main-integrate-push-health` — integrate `origin/main` safely, then push commit `d611b81`.
- [ ] `health-basic-auth-or-sso` — choose quick `nginx basic auth` vs SSO front door.
- [ ] `monitoring-authentik-pilot` — if SSO is selected, pilot `authentik` with local users before AD/LDAP.
- [ ] `health-dashboard-usage-cost-exact` — import fresh Cursor Usage CSV and calculate exact cost for this chat.

## GitLab reorg + printer/Zabbix (from 2026-06-20 chat)

- [ ] `gitlab-registry-cleanup-then-transfer` — move blocked projects after container registry tag cleanup (`apps/sana-gps`, `apps/tile-server`, `apps/cms-site-builder`, `apps/shop-app`).
- [ ] `printer-172.16.1.141-network` — investigate/restore device reachability (ARP/ICMP unreachable from monitoring and client LAN).
- [ ] `gitlab-post-reorg-cleanup` — validate and optionally delete empty/duplicate leftovers (`prompts/map`, possible duplicate `iot/tracker/voip-out`).

## GPS / Activity closeout (from 2026-06-20 chat)

- [ ] `device-sana-gps-service-root-cause` — `device.sana-gps.ir` ping دارد اما HTTP/HTTPS و پورت‌های محتمل timeout شدند؛ NAT/Firewall/MikroTik و سرویس پشت `94.182.193.115` بررسی شود.
- [ ] `device-sana-gps-port-inventory` — پورت واقعی سرویس GPS/Traccar/device listener از config عملیاتی یا MikroTik NAT استخراج و در runbook ثبت شود.
- [ ] `cursor-usage-exact-chat-cost` — بعد از بسته شدن چت، CSV جدید Cursor Usage بدون cookie خام import شود تا eventهای انتهایی closeout هم حساب شوند.
- [ ] `rotate-cursor-web-session` — چون cookie/session token داخل prompt paste شده، session مرورگر Cursor logout/login یا revoke شود.

## Metabase / Nextcloud / Stage (from 2026-06-10 chat)

- [ ] **persist-stage-fixes** — nginx redirect guard, cookie_path, apache :18062 in debops templates
- [ ] **cypress-e2e-verify** — run `nextcloud/e2e` docker compose locally
- [ ] **disk-monitor-stage** — alert when `/` on 172.16.1.150 > 80%
- [ ] **ca-client-rollout** · **mikrotik-dns-verify** · **elk-via-nexus** · **bi-bot-cleanup**

Detail: [todos/debops-metabase-nextcloud-stage-chat-open.md](todos/debops-metabase-nextcloud-stage-chat-open.md)

## Nexus / OpenVPN (from 2026-06-10 chat)

- [ ] `nexus-apt-noble` — apt via Nexus on stage (404 / InRelease)
- [ ] `nexus-mikrotik-dns` — `MIKROTIK_PASSWORD` + script
- [ ] `nexus-elk-e2e` — pull-elk-via-nexus.sh monitoring
- [ ] `nexus-client-jira-mm-voip` — unreachable hosts
- [ ] `nexus-admin-password` — stable password in `.env`
- [ ] `nexus-gitlab-ci-vars` — GitLab UI variables
- [ ] `nc-mikrotik-openvpn-deploy` — occ enable on NC server
- [ ] `nexus-vpn-onboarding` — developer doc file
- [ ] `nexus-client-smoke` — one clean dev VM test

Detail: [todos/2026-06-10-11-nexus-openvpn-chat-open.md](todos/2026-06-10-11-nexus-openvpn-chat-open.md)

## VoIP 9902 / Metabase / Grafana (2026-06-16 chat)

- [ ] `metabase-dashboard-9902` — UI datasource + dashboard
- [ ] `grafana-wallboard-verify` — http://172.16.1.164:3000/d/support-queue-9902-wallboard
- [ ] `freepbx-ivr-key3-smoke` — members 302–305
- [ ] `env-wordpress-db-password` + cron log check
- [ ] `debops-commit-split` — analytics, Makefile, activity log

Detail: [todos/2026-06-16-debops-voip-analytics-chat-open.md](todos/2026-06-16-debops-voip-analytics-chat-open.md)

## Stage-deployer production / UI / webhook (from 2026-06-18–20 chat)

- [ ] **push stage-deployer** — ~15 commits ahead of origin; push GitLab when ready
- [ ] **webhook secrets** — GitLab #1597 / GitHub `403` → align secrets with `.env`
- [ ] **SMS alarm env** — `WEBHOOK_ALERT_SMS_MOBILE`, `ALERT_SMS_HTTP_URL` (user sets `.env`)
- [ ] **Nextcloud submodule remote** — create `infra/nextcloud.git` + push submodule
- [ ] **Production SSH** — `prod_host`/`prod_path` for erp-guarantie & mix-proj if real prod deploy
- [ ] **AD LDAP Nextcloud** — `occ ldap:test-config` still failing (DC/network)
- [ ] **Rotate Cursor session** — cookie pasted again in 2026-06-20 closeout prompt

Detail: [todos/2026-06-18-stage-deployer-production-ui-chat-open.md](todos/2026-06-18-stage-deployer-production-ui-chat-open.md)

## Sana-GPS / MikroTik / Monitoring (from 2026-06-14 .. 2026-06-20 chat)

- [ ] `mikrotik-dstnat-harden-interface` — convert key NAT rules from fixed `dst-address=94.182.193.115` to `in-interface=pppoe-out-Shatel-Radio` for IP-change resilience.
- [ ] `mikrotik-postchange-backup` — run `./mikrotik/backup-mikrotik.sh` after live RouterOS edits made during outage recovery.
- [ ] `watchdog-e2e-controlled-test` — run one controlled outage simulation to verify full chain (SMS first, voice call after 60s).
- [ ] `arvan-sana-api-access` — provision correct Arvan API key with access to `sana-gps.ir` for scripted origin checks/updates.

## Passbolt / DNS (from 2026-06-19 chat)

- [ ] `passbolt-dns-mikrotik` — set `passbolt.monitoring.artandev.ir` → `172.16.1.164` on MikroTik (`172.16.1.1`); live check resolved to `172.16.1.134`.
- [ ] `passbolt-dns-windows` — add/fix `passbolt.artandev.ir` on Windows DNS (`172.16.1.246`); was `NXDOMAIN` during live check.
- [ ] `passbolt-traefik-18780-login` — Traefik on `:18780` returned `404` for `/auth/login`; direct `:8443/auth/login/` works.
- [ ] `passbolt-runbook-url` — update `debops/mikrotik/runbooks/INTERNAL-MONITORING.md` with working URL and trailing-slash note.

## Network / GitLab / stage Docker (from 2026-06-20 chat)

- [ ] **modular-gps subnet migration** — recreate stack with `infrastructure/docker-compose.stage-net-pin.yml` (`192.168.208.0/20`); maintenance window.
- [ ] **Commit debops mahdi-apps guard** — `scripts/infra/mahdi-apps/templates/*` + `apply-stage-erp-docker-net-guard.sh` (currently uncommitted in debops).
- [ ] **MikroTik backup** — if live filter rule for GitLab was added manually: `./mikrotik/backup-mikrotik.sh`.
- [ ] **Do NOT auto-apply `default-address-pools`** on stage Docker without test — broke dockerd once (`fully subnetted`); see `templates/stage-docker-daemon.json.example`.
- [x] **gitlab-vm-route.service** on stage — `/32` route to GitLab VM.
- [x] **erp-guarantie compose pinning** — templates + `compose.sh` on stage.

## High Priority

- [ ] **Stage-deployer chat (2026-06-07)** — GitLab CI green, System Hook, mix-proj CI, stage-deployer push — [detail](todos/2026-06-07-stage-deployer-chat-open.md)
- [ ] **Stage health/deploy chat (2026-06-19)** — modular-gps build, stage-deployer push, sanaradyab-ws 502 — [detail](todos/2026-06-19-stage-health-deploy-chat-open.md)
- [ ] **Persian SMS end-to-end test** — send one controlled Persian SMS through the active Novin `send-sync` path and verify the phone renders Persian, not `???`.
- [ ] **Recover old `???` Persian files only if source exists** — old reports/templates with literal question marks cannot be decoded back automatically.
- [ ] **Split and commit `debops` work safely** — the working tree contains many unrelated changes; split by topic and scan for secrets before any commit/push.
- [ ] **Critical backup activation** — keep heavy backups disabled until storage is available; enable only via explicit flags and dry-run first.

## Debops Backup System

- [ ] `backup-dry-run-monitoring` — run `daily-critical-backup.sh --dry-run` on monitoring with the current flag file.
- [ ] `backup-heavy-storage-plan` — define storage destination, retention, and capacity for heavy jobs.
- [ ] `backup-zabbix-stale-alert` — add alert for stale/missing backup manifests after real backup scheduling is approved.
- [ ] `sana-gps-postgres-heavy` — deploy and test heavy PostgreSQL backup path only after storage decision.

## Monitoring / Encoding

- [x] `backup-control-utf8` — fixed UTF-8 content and live Nginx `charset=utf-8`.
- [x] `audit-page-utf8` — fixed UTF-8 content and live Nginx `charset=utf-8`.
- [x] `sms-json-utf8-headers` — changed active SMS scripts to send `application/json; charset=utf-8`.
- [ ] `legacy-report-cleanup` — regenerate old corrupted Persian reports from source data where possible.

## Nexus / Developer Access

- [ ] `nexus-vpn-onboarding` — turn the VPN + Nexus usage notes into a short developer onboarding page.
- [ ] `nexus-client-smoke` — test `setup-nexus-client.sh` on one clean dev VM/laptop profile.

## Stage / AD / Adminer Follow-ups From Earlier Prompts

- [ ] `ad-nextcloud-bind` — finish `nextcloud-svc` bind and `occ ldap:test-config s01`.
- [ ] `ad-login-smoke` — verify AD login to Stage dashboards and `devops_portal`.
- [ ] `analytics-db-localhost` — fix or document `support_analytics@localhost` vs `@%`.
- [ ] `stage-db-adminer-map` — provide clear Adminer access path for Stage DBs via monitoring.
- [ ] `stage-db-proxy` — decide whether to publish/socat MySQL/Postgres from Stage for Adminer.

## Arvan / Edge / SSL

- [ ] `arvan-dns-export` — export all registrar/Arvan DNS records before CDN cutover work.
- [ ] `arvan-edge-vps-wg-le` — pilot VPS edge with WireGuard and Let's Encrypt, without touching production DNS first.
- [ ] `arvan-cutover-doc` — write reversible cutover/rollback checklist.
- [ ] `gps-tile-no-cdn-plan` — handle GPS/tile traffic separately from simple web SSL replacement.

## VM Factory / OSS Infrastructure

- [ ] `esxi-golden-docker-2404` — build a golden Ubuntu/Docker/Nexus-ready image.
- [ ] `esxi-vm-factory-mvp` — simple VM creation flow before evaluating larger platforms.
- [ ] `proxmox-eval-migrate` — evaluate Proxmox as UI-first VM factory.
- [ ] `foreman-content-nexus` — evaluate Foreman/Katello only if content lifecycle needs justify it.

## Security Reminder

- [ ] **Rotate Cursor web session** — browser cookie/session token was pasted into chat (network closeout + prior chats). Logout/login or revoke.

## stage-subdomain-github-webhook (from 2026-06-20 chat)

- [ ] **GitHub webhook Secret** — align GitHub with `GITHUB_WEBHOOK_SECRET` in stage-deployer `.env`
- [ ] **subdomain modular-gps** — nginx + env or stage-deployer feature
- [ ] **push stage-deployer** — webhook secret loading fixes
- [ ] **Rotate Cursor session** — cookie pasted in chat

Detail: [todos/2026-06-20-stage-subdomain-github-webhook-chat-open.md](todos/2026-06-20-stage-subdomain-github-webhook-chat-open.md)

**Last updated:** 2026-06-20 (stage-subdomain-github-webhook closeout)

## debops-sana-gps-nat (from 2026-06-21 chat)

- [ ] `rotate-cursor-web-session` — چون Cookie/Session Cursor داخل چت paste شد، session مرورگر Cursor logout/login یا revoke شود.
- [ ] `cursor-usage-exact-2026-06-21` — CSV جدید Cursor Usage برای 2026-06-21 با روش امن `~/.config/cursor-usage.env` گرفته شود.
- [ ] `llfs-global-user-rule` — اگر ابزار User Rule سراسری Cursor در workspace بعدی در دسترس بود، مفهوم `llfs close TOPIC` به User Rule سراسری اضافه شود.
- [ ] `sana-gps-nat-runbook` — ruleهای ضروری Traccar listener (`5015`, `5023`, `7700` TCP/UDP به `172.16.1.134`) در runbook عملیاتی ثبت شوند.
- [ ] `sana-gps-nat-monitor` — مانیتور ingestion واقعی اضافه شود: recent positions، online count و NAT counters، نه فقط HTTP 200.

Detail: [todos/2026-06-21-debops-sana-gps-nat-chat-open.md](todos/2026-06-21-debops-sana-gps-nat-chat-open.md)

**Last updated:** 2026-06-21 (debops-sana-gps-nat closeout)
