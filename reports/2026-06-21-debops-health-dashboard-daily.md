# Daily git report — debops health dashboard — 2026-06-21

<div dir="rtl" style="text-align: right;">

**Repo:** `/opt/new/debops`  
**Focus:** monitoring Health portal, internal runbook, SSO discussion, UI polish

---

## Git history

| Date | Commit | Message |
|------|--------|---------|
| 2026-06-21 | `d611b81` | `Polish health dashboard glass styling` |

## Main code/doc changes

| Path | Summary |
|------|---------|
| `monitoring/portal/health.html` | Glassmorphism styling: layered background, blur, translucent cards, hover states and glow. |
| `monitoring/portal/nginx.conf` | Serves `/INTERNAL-MONITORING.md` from the portal. |
| `scripts/monitoring/deploy-health-dashboard.sh` | Copies/mounts the internal monitoring runbook alongside Health assets. |
| `mikrotik/runbooks/INTERNAL-MONITORING.md` | Lists internal monitoring URLs and default/initial credentials, while keeping real production secrets in handover. |

## Operational notes

- Health page deploy was not executed in this chat.
- `git push` for debops failed because `main` is non-fast-forward (`ahead 1, behind 2`).
- Dirty working tree contains many unrelated changes; integrate remote carefully before pushing `d611b81`.
- Nextcloud remains blocked as an identity portal until AD bind / StartTLS is fixed.

## Recommended next steps

1. Decide whether Health gets quick `nginx basic auth` or a proper SSO front door.
2. If SSO is required, pilot `authentik` on monitoring with local users first, then connect AD/LDAP later.
3. Resolve debops `main` divergence safely and push `d611b81`.
4. Run the Health deploy script and smoke-test:

```bash
bash scripts/monitoring/deploy-health-dashboard.sh
```

</div>
