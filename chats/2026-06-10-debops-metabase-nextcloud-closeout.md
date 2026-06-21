# Debops chat closeout � Metabase / Nextcloud / Stage

**Workspace:** `/opt/new/debops`  
**Transcript:** `351ee9ef-bdc6-434d-8891-227c43eae6d0`  
**Dates:** 2026-06-10 � 2026-06-11 (+0330)

## Prompt summary

| # | Topic | Result |
|---:|---|---|
| 1 | Metabase strategic dashboards | 4 dashboards, 34 SQL questions on `172.16.1.134:3001` |
| 2 | Nextcloud devops_portal | App deployed; source in `nextcloud/devops_portal/` |
| 3 | NC admin login 303 | NC29 app fix, cookie_path, RewriteBase |
| 4 | Page not found / 302 on IP | Wrong URL `:18062/nextcloud/` |
| 5 | Cypress e2e local docker compose | Scaffold only � **not verified** |
| 6 | stage.artandev.ir down | VM hang, disk 100%, wrong DNS |
| 7 | ESXi reboot + DNS | Infra2 reset, docker prune, user fixed MikroTik |
| 8 | HTTPS blank page | Redirect loop fix; Artan Internal Root CA export |
| 9 | Session token expired | Redis/config fix |
| 10 | 502 | Redis/DB + stage-deployer `:9080` restored |

## What to learn

1. Nextcloud subpath: use `/nextcloud/` via nginx, not `:18062/nextcloud/`
2. `cookie_path=/nextcloud` required for sessions under subpath
3. NC29: no `registerController()` in custom apps
4. HTTP?HTTPS on `:80` must skip when `X-Forwarded-Proto: https`
5. Install internal CA or browsers show blank page (not always a cert warning)
6. Full disk ? Redis MISCONF ? 502; watch docker image growth on stage
7. MikroTik wildcard DNS can send stage to wrong host (134 vs 150)
8. GPS Metabase is `134:3001`, not monitoring `164:3001`

## Security

- Rotate Cursor session (cookie pasted in chat)
- Review Metabase `bi-bot@sanaradyab.ir` service account

## llfs links

- [TODO](../cursor/todos/debops-metabase-nextcloud-stage-chat-open.md)
- [Cost](../cursor/usage/debops-metabase-nextcloud-2026-06-10_2026-06-11-cost-by-prompt.md)
- [Daily report](../reports/2026-06-10-11-debops-daily.md)
