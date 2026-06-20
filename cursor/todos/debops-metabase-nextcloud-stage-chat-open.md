# Open TODOs — debops Metabase / Nextcloud / Stage chat

**Chat transcript:** `351ee9ef-bdc6-434d-8891-227c43eae6d0`  
**Started:** 2026-06-10 (19:55 +0330)  
**Workspace:** `/opt/new/debops`

## Must do (from this chat)

- [ ] **persist-stage-fixes** — commit repo templates for fixes applied only on server:
  - nginx `stage-router.conf`: HTTP?HTTPS redirect with `X-Forwarded-Proto != https` guard
  - Nextcloud `cookie_path=/nextcloud`, `overwriteprotocol`, `overwritehost`
  - Apache redirect `:18062/nextcloud/*` ? `http://172.16.1.150/nextcloud/*`
  - `docker-compose.stage.yml` / integrate script updates
- [ ] **cypress-e2e-verify** — run `nextcloud/e2e/docker-compose.yml` locally; confirm admin login test passes against stage URL
- [ ] **disk-monitor-stage** — alert or cron on `172.16.1.150` when `/` > 80% (docker images were ~67GB before prune)
- [ ] **ca-client-rollout** — distribute `certs/artan-internal-root-ca.crt` to PCs; document install steps for internal HTTPS
- [ ] **mikrotik-dns-verify** — spot-check LAN clients: `stage.artandev.ir` must not resolve to `172.16.1.134`

## Optional / follow-up

- [ ] **metabase-filters-email** — dashboard date filters + weekly email for managers
- [ ] **portal-sync-8090** — align monitoring portal `:8090` links with `devops_portal`
- [ ] **elk-via-nexus** — run `scripts/pull-elk-via-nexus.sh` when ready
- [ ] **bi-bot-cleanup** — disable/remove `bi-bot@sanaradyab.ir` in Metabase if no longer needed
- [ ] **gitlab-links-api** — refresh GitLab project links in devops_portal from API
- [ ] **debops-split-commit** — many uncommitted debops changes; split by topic, scan for secrets before push

## Done in chat (reference)

- Metabase: 4 dashboards + 34 SQL questions on `172.16.1.134:3001`
- Nextcloud `devops_portal` app deployed on stage
- Stage VM reboot (ESXi Infra2), disk prune, Nextcloud/502/session fixes
- Internal CA exported to `certs/artan-internal-root-ca.crt`
