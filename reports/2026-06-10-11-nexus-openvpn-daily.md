# Daily git report � Nexus + OpenVPN chat

**Repo:** `/opt/new/debops`  
**Transcript:** `82328549-acd5-4f23-9467-1f30d653c78c`  
**Period:** 2026-06-10 .. 2026-06-11

## Commits on main

None on 2026-06-10/11 for Nexus work. Nearest: `e979982` (2026-06-11, Zabbix SMS).

## Uncommitted deliverables

| Path | Note |
|---|---|
| `scripts/setup-nexus-repos.sh` | Nexus bootstrap |
| `scripts/setup-nexus-client.sh` | Client mirror + CA |
| `scripts/windows-dns-nexus.py` | Windows DNS A record |
| `scripts/mikrotik-dns-nexus.sh` | MikroTik DNS (needs password) |
| `docs/NEXUS-USAGE.md` | Usage doc |
| `nextcloud/mikrotik_openvpn/` | NC OpenVPN admin app |

## Activity log

| Date | Timing | Topic | Summary |
|---|---|---|---|
| 1405/03/20 | Outside office hours | Nexus | Reset + repos + nginx 5000�5003 |
| 1405/03/20 | Outside office hours | Clients | nexus, gitlab, monitoring, sana-gps, stage OK (Docker) |
| 1405/03/20 | Outside office hours | DNS | Windows OK; MikroTik pending |
| 1405/03/20 | Outside office hours | NC app | mikrotik_openvpn PPP secret CRUD |

## Next git actions

1. Commit Nexus scripts/docs as focused topic.
2. Commit `mikrotik_openvpn` separately.
3. Never commit `.env` or cookies.
