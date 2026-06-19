# گزارش Git روزانه — stage-deployer + devops

**بازه:** 2026-06-18 … 2026-06-20 (+0330)  
**VM:** infra2 `/opt/stage-deployer`  
**مرتبط با چت:** `b8973106-6874-49f9-8474-e93f694cb515`

---

## stage-deployer (`infra/stage-deployer`)

### Commits محلی (مهم)

| Hash | زمان (+0330) | پیام |
|------|------------|------|
| `20e3d8d` | 2026-06-18 04:40 | Add devops submodule + DevOps Health UI |
| `3cab41f` | 2026-06-18 20:33 | **Release 0.7.0** — VERSION, GITLAB-COMMANDS, deploy fixes |
| `b646663` | 2026-06-18 20:33 | bump devops submodule pointer |
| `1613cf7` | 2026-06-18 20:33 | docs: GITLAB-COMMANDS |
| `1a7fe82` | 2026-06-18 20:34 | fix(docker): mount VERSION |
| `3879bea` | 2026-06-18 | project hook accordion, hash routing |
| `5fd524d` | 2026-06-18 | deploy pipeline / System Hook accuracy |
| `92c0391` | 2026-06-19 | stage auto-heal cron |
| `54e84d0` | 2026-06-19 | deploy history + sticky sidebar |
| `4ee2101` | 2026-06-20 | project archive system |

### Remote

- **Branch pushed:** `release/0.7.0` ✓
- **Tag:** `v0.7.0` ✓
- **`main`:** protected — MR لازم
- **Remote URL:** `ssh://git@192.168.160.30:2222/infra/stage-deployer.git`

### MR

https://git.artandev.ir/infra/stage-deployer/-/merge_requests/new?merge_request%5Bsource_branch%5D=release%2F0.7.0

---

## devops (`infra/devops`)

| Hash | زمان | پیام |
|------|------|------|
| `383f570` | 2026-06-18 | cursor-prompt-metrics + stage health (rebased) |
| `e876ce5` | 2026-06-18 | fix install-workspace.sh |

- **Push:** `main` ✓ (merge با ESXi/VoIP/MikroTik موجود)

---

## پرامپت → commit mapping

| پرامپت | commits / artifacts |
|--------|---------------------|
| prompt-metrics محلی | devops `cursor-prompt-metrics/*` |
| devops submodule + /opt cleanup + health menu | `20e3d8d`, devops `383f570` |
| VERSION + GitLab history | `3cab41f`, `docs/GITLAB-COMMANDS.md` |
| GitLab push when up | `release/0.7.0`, devops push `e876ce5` |

---

## باز

- MR `release/0.7.0` → `main`
- `nextcloud/`, `docs/opt-handoff/` uncommitted
- TODO: `cursor/todos/2026-06-18-opt-devops-chat-open.md`
