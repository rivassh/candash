# Stage-deployer chat closeout — 2026-06-07

**Workspace:** `/opt` (infra2, stage-deployer)  
**Transcript:** `1f3fb1df-2297-45af-978c-92fc489fd128`  
**Scope:** webhook، Nextcloud، migrate domain، GitHub auto-deploy، Nextcloud login، timezone، AI deploy rules، GitLab CI analysis، مستند روال

## Prompt Summary (38 user messages)

| Phase | Key asks | Outcome |
|---|---|---|
| Webhook basics | listen? secret? log all requests | logging OK; token verify |
| Nextcloud | dashboard integration, AD login | stack up; LDAP disabled (AD down) |
| Domain | stage.artandev.ir, MikroTik port | migrated; 443/8000 |
| GitHub | 403 fix, minicrm hook, auto deploy | `/webhook/github`; async mirror+deploy |
| Runtime | docker compose not systemd | compose deployer |
| Ops | Nextcloud session, server time | login fixed; Asia/Tehran |
| mix-proj | webhook ignored on main | System Hook + STAGE_REF_EXCLUDE |
| AI workflow | rules all /opt, prompt for stage | rules pushed ~15 repos |
| CI | red X on GitLab | root cause doc; not fixed yet |
| Closeout | TODO, cost, llfs, learnings | this bundle |

## What To Learn From This Chat

چیزهایی که احتمالاً **قبل از این چت روشن نبود**:

1. **دو مدل URL استیج:** path (`/mix-proj/`) vs ref subdomain (`feature-x.stage.artandev.ir`) — CI و webhook باید جدا فکر شوند.

2. **push به `main` از webhook deploy نمی‌شود** — `STAGE_REF_EXCLUDE=main,master,develop`. برای path-based باید `deploy-project.sh {slug} main` (agent/دستی).

3. **System Hook ≠ Project Webhook** — GitLab System Hook با `object_kind: push` می‌آید ولی `X-Gitlab-Event: System Hook` → handler رد می‌کند.

4. **GitHub webhook URL جدا است:** `/webhook/github` نه `/webhook/gitlab`. Secret جدا (`GITHUB_WEBHOOK_SECRET`).

5. **تغییر `.env` → restart container deployer** — bind mount فایل را می‌خواند ولی process env از startup است.

6. **Nextcloud + AD:** StartTLS به DC down → کل login می‌شکند؛ موقت LDAP off + local admin.

7. **GitLab CI قرمز ≠ استیج down** — verify روی URL اشتباه/دامنه قدیمی timeout می‌خورد؛ runner/secret جدا از stage-deployer.

8. **stage-deployer خودش slug نیست** — push به repo deployer ≠ redeploy همه پروژه‌ها؛ اسکریپت mount‌شده با pull اعمال می‌شود.

9. **هزینه Cursor در export:** `Included` — ~838M token در هفته ≠ دلار مستقیم در CSV.

10. **هرگز session cookie Cursor در چت نفرست** — rotate بعد از closeout.

## Links

- TODO: [cursor/todos/2026-06-07-stage-deployer-chat-open.md](../cursor/todos/2026-06-07-stage-deployer-chat-open.md)
- Cost: [cursor/usage/2026-06-07-stage-deployer-cost-by-prompt.md](../cursor/usage/2026-06-07-stage-deployer-cost-by-prompt.md)
- CSV: [cursor/usage/2026-06-14_2026-06-20-stage-deployer.csv](../cursor/usage/2026-06-14_2026-06-20-stage-deployer.csv)
- Git daily: [reports/2026-06-07-stage-deployer-git-daily.md](../reports/2026-06-07-stage-deployer-git-daily.md)
- Stage process doc: (در چت تولید شد — می‌توان به llfs اضافه کرد)

## Security

- Session token در closeout paste شد — در llfs commit نشده. logout/login Cursor توصیه می‌شود.
