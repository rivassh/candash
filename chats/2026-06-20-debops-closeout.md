# Debops chat closeout — 2026-06-20

**Workspace:** `/opt/new/debops`  
**Transcript:** `75045d56-a947-49a1-b7ac-5f006365f58a`  
**Scope:** monitoring, Vault, Arvan 502, dashboards, Nextcloud SMS, Nexus, Cursor usage tracking, critical backups, backup-control UI, UTF-8 Persian fixes.

## Prompt Summary

| # | Date | User prompt/theme | Result |
|---:|---|---|---|
| 1 | 2026-06-16 | Monitoring login details | Listed verified monitoring services and SSH path; did not expose credentials. |
| 2 | 2026-06-16 | Dashboard linking all monitoring systems | Confirmed `172.16.1.164:8090` portal and direct links. |
| 3 | 2026-06-16 | Web accounts and passwords | Redirected credential retrieval to Passbolt/Vault. |
| 4 | 2026-06-16 | Vault login | Verified token-based dev-mode Vault login. |
| 5 | 2026-06-16 | `guarantee.sana-gps.ir` 502 | Diagnosed Arvan origin issue; origin `31880` was healthy. |
| 6 | 2026-06-16 | CEO/CTO health dashboard UI | Upgraded and deployed the monitoring health dashboard. |
| 7 | 2026-06-16 | Activity log from chat | Produced project-style activity log. |
| 8 | 2026-06-17 | Recurring Nextcloud LDAP SMS | Traced alert to monitoring watchdog/cron rather than Zabbix. |
| 9 | 2026-06-19 | Internal Nexus usage | Documented Docker/npm/pip/apt use of `nexus.lan`. |
| 10 | 2026-06-19 | Nexus for outside developers | Recommended OpenVPN access path before Nexus usage. |
| 11 | 2026-06-19 | Cursor usage tracker rollout | Added tracker workflow to playbook/submodule and projects. |
| 12 | 2026-06-19 | `commit push` | Committed and pushed tracker/playbook changes. |
| 13 | 2026-06-19 | Existing backups report | Audited backup coverage and gaps for critical systems. |
| 14 | 2026-06-19 | Daily critical backup plan | Designed light/heavy backup strategy with heavy jobs disabled by flag. |
| 15 | 2026-06-19 | Where to toggle backup jobs | Explained env/flag control path. |
| 16 | 2026-06-19 | Web page to toggle backup jobs | Added `backup-control.html`, API, deploy script. |
| 17 | 2026-06-20 | Monitoring IP URL | Provided `http://172.16.1.164:8090/backup-control.html`. |
| 18 | 2026-06-20 | Persian text appears as `???` | Repaired UTF-8 text in portal pages and SMS JSON headers; deployed. |
| 19 | 2026-06-20 | Today's report | Produced daily activity report. |
| 20 | 2026-06-20 | Close chat, TODO, cost, llfs reports | Created closeout documentation and pushed to `llfs`. |

## What To Learn From This Chat

These are the main patterns that were repeatedly useful and are worth turning into habit:

- **Credentials are not documentation.** URLs, usernames, and access paths can be documented, but passwords/tokens should stay in Passbolt/Vault or be read directly from the host only when needed.
- **For production incidents, prove the path before changing anything.** The `guarantee.sana-gps.ir` issue looked like an app outage, but the origin was healthy and the CDN/origin-port mapping was the real suspect.
- **Separate live facts from repo facts.** For infra, read the live service/container state and compare it with repo docs before answering.
- **Encoding bugs can be irreversible.** If Persian text is already saved as `???`, the original characters are lost unless there is another source. Prevention means `UTF-8` files plus `charset=utf-8` HTTP headers plus UTF-8 JSON/SMS payloads.
- **Heavy backups need explicit activation.** Treat DB dumps, recordings, registries, TSDBs, and blob stores as storage projects, not as simple cron jobs.
- **A web toggle is not the backup system.** The UI should only change flags; real backup execution still needs a controlled script, dry-run, destination, and retention policy.
- **Nexus outside the company should go through VPN first.** Exposing package mirrors publicly is usually more risk than benefit.
- **Long Cursor chats become expensive mostly through cache reads.** Scope the next prompt, start fresh chats for unrelated work, and reuse CSV exports instead of repeated dashboard fetches.

## Security Notes

- A browser cookie/session token was pasted in the final prompt. It was not written to any report. The session should be rotated by logging out/in to Cursor after this handoff.
- The cost report intentionally stores only aggregate CSV-derived usage fields, not authentication headers.

## Useful Links

- Backup control page: `http://172.16.1.164:8090/backup-control.html`
- Health dashboard: `http://172.16.1.164:8090/health.html`
- Audit dashboard: `http://172.16.1.164:8090/audit.html`
- Cost report: `cursor/usage/debops-2026-06-14_2026-06-20-cost-by-prompt.md`
- Daily report: `reports/2026-06-20-debops-daily.md`
