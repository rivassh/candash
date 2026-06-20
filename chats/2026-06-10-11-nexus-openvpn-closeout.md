# Debops chat closeout — Nexus rollout + Nextcloud OpenVPN

**Workspace:** `/opt/new/debops`  
**Transcript:** `82328549-acd5-4f23-9467-1f30d653c78c`  
**Timestamp (only explicit):** 2026-06-10 20:11 +0330  
**Scope:** Internal Nexus (`172.16.1.210`), client rollout, DNS, developer guides, Nextcloud `mikrotik_openvpn` app.

## Prompt summary

| # | Topic | Result |
|---:|---|---|
| 1 | All projects should use internal Nexus | Reset broken Nexus DB/auth; fresh install + repos |
| 2 | Start / continue | `setup-nexus-repos.sh`, EULA, docker/apt/pypi/npm, nginx 5000–5003 |
| 3 | Continue rollout | Client scripts, docs, multi-host rollout |
| 4 | STAGE_SUDO_PASSWORD; MikroTik | Windows DNS OK; MikroTik DNS failed (no password) |
| 5 | Guide developer for new project | Onboarding checklist (Docker/pip/npm/CI) |
| 6 | Remote developers | VPN + DNS + CA model; CI-only fallback |
| 7 | NC app: OpenVPN users on MikroTik | `nextcloud/mikrotik_openvpn/` |
| 8 | Activity log | Project activity table |
| 9 | Close chat | This llfs bundle |

## What to learn

1. Nexus may need **full data reset** if H2 DB / admin auth is broken.
2. Client trust needs **OS CA + Docker certs.d** both.
3. **`sudo -S` + stdin pipes** break CA install — use temp files.
4. **docker group ? root** on monitoring — exceptional path only.
5. **Windows DNS ? MikroTik DNS** — verify each resolver separately.
6. **apt via Nexus** harder than Docker — do Docker/npm/pip first.
7. **Remote devs need VPN** before Nexus URLs work.
8. **NC infra apps** — encrypt SSH creds; delegate via NC group.
9. **Long infra agent chats** are token-heavy — split by topic.
10. **Never paste Cursor cookies** — use local `cursor-usage.env`; rotate session.

## Related files

- [cursor/todos/2026-06-10-11-nexus-openvpn-chat-open.md](../cursor/todos/2026-06-10-11-nexus-openvpn-chat-open.md)
- [cursor/chats/2026-06-10-11-nexus-openvpn-prompts.md](../cursor/chats/2026-06-10-11-nexus-openvpn-prompts.md)
- [cursor/usage/nexus-openvpn-2026-06-10-11-cost-by-prompt.md](../cursor/usage/nexus-openvpn-2026-06-10-11-cost-by-prompt.md)
- [reports/2026-06-10-11-nexus-openvpn-daily.md](../reports/2026-06-10-11-nexus-openvpn-daily.md)
