# TODO � Nexus rollout + Nextcloud OpenVPN chat (open)

**Transcript:** `82328549-acd5-4f23-9467-1f30d653c78c`  
**Dates:** 2026-06-10 (20:11 +0330)  
**Closeout:** [chats/2026-06-10-11-nexus-openvpn-closeout.md](../../chats/2026-06-10-11-nexus-openvpn-closeout.md)

## Nexus infrastructure

- [ ] `nexus-apt-noble` � apt via Nexus on stage/Ubuntu noble: 404 / InRelease timing
- [ ] `nexus-mikrotik-dns` � `MIKROTIK_PASSWORD` in `.env`; run `scripts/mikrotik-dns-nexus.sh`
- [ ] `nexus-elk-e2e` � verify `./scripts/pull-elk-via-nexus.sh monitoring`
- [ ] `nexus-client-jira-mm-voip` � `setup-nexus-client.sh` on jira, mattermost, voip
- [ ] `nexus-admin-password` � stable `NEXUS_ADMIN_PASSWORD` in `.env`
- [ ] `nexus-gitlab-ci-vars` � GitLab CI variables in UI

## Developer onboarding

- [ ] `nexus-dev-onboarding-doc` � `docs/NEXUS-DEVELOPER-GUIDE.md` (LAN + remote/VPN)
- [ ] `nexus-client-smoke` � test `setup-nexus-client.sh` on one dev VM/laptop

## Nextcloud

- [ ] `nc-mikrotik-openvpn-deploy` � deploy `mikrotik_openvpn` + `occ app:enable`
- [ ] `nc-devops-portal-deploy` � confirm `devops_portal` on production NC

## Security

- [ ] **Rotate Cursor session** � cookie pasted in close prompt
