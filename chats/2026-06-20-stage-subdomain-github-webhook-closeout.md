# Chat closeout — stage-subdomain-github-webhook — 2026-06-20

**Workspace:** `/opt/stage-deployer`  
**Generated:** 2026-06-20T21:45:00Z

## Learnings (AGENT — max 5 bullets)

1. **stage-deployer پیش‌فرض path-based است** (`stage.artandev.ir/{slug}/`); modular-gps به زیردامنه + `APP_*_HOSTS` نیاز دارد — path کافی نیست.
2. **GitLab webhook ≠ GitHub** — GitLab: `GITLAB_WEBHOOK_SECRET` + header token؛ GitHub: `GITHUB_WEBHOOK_SECRET` + HMAC `X-Hub-Signature-256`.
3. **`docker compose restart` env را reload نمی‌کند** — بعد از `.env` باید `docker compose up -d --force-recreate` بزنی.
4. **Secret با `;` در `.env` باید quote شود** — و در docker باید با mounted `.env` (force_keys) هم‌خوان باشد.
5. **invalid signature با سرور سالم = Secret در GitHub با `.env` یکی نیست** — curl با secret درست از `https://stage.artandev.ir/webhook/github` → `accepted: true`.

## Prompt summary (AGENT — optional)

| # | Theme | Result |
|---:|---|---|
| 1 | subdomain برای modular-gps بدون تغییر base code | nginx ثابت + env + DNS؛ یا feature آینده در stage-deployer |
| 2 | پروژه subdomain جدید از داشبورد | path-only کافی نیست؛ setup per پروژه تا feature خودکار |
| 3 | briefing stage-deployer برای AIهای دیگر | خلاصه قابل copy در چت |
| 4 | GitHub webhook invalid signature | env stale + Secret mismatch؛ fix + recreate |
| 5 | llfs close اتومات | `close-chat.sh` + rule |

## Artifacts

| Type | Path |
|------|------|
| Git daily | [`reports/2026-06-20-stage-subdomain-github-webhook-git-daily.md`](../reports/2026-06-20-stage-subdomain-github-webhook-git-daily.md) |
| Cost | [`cursor/usage/stage-subdomain-github-webhook-2026-06-13_2026-06-20-cost.md`](../cursor/usage/stage-subdomain-github-webhook-2026-06-13_2026-06-20-cost.md) |
| TODO | [`cursor/todos/2026-06-20-stage-subdomain-github-webhook-chat-open.md`](../cursor/todos/2026-06-20-stage-subdomain-github-webhook-chat-open.md) |
| Prompts | [`cursor/chats/2026-06-20-stage-subdomain-github-webhook-prompts.md`](../cursor/chats/2026-06-20-stage-subdomain-github-webhook-prompts.md) |

## Security

- Never commit Cursor session cookies.
- CSV: failed — set `CURSOR_USAGE_COOKIE` in `~/.config/cursor-usage.env`
