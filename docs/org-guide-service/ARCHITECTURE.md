# Org Guide Service — Architecture (summary)

Full detail: [`/opt/org-guide-service/ARCHITECTURE.md`](/opt/org-guide-service/ARCHITECTURE.md)

## What it is

Central guide host (`embed.js` + tour JSON) consumed by all Artan Dev web apps — same architectural slot as the support ticket widget.

## Key paths

| Path | Purpose |
|------|---------|
| `/opt/org-guide-service/` | Service repo (MVP) |
| `public/embed.js` | Embed script |
| `tours/callcenter/getting-started.json` | Sample callcenter tour |
| `snippets/` | Laravel / Next / nginx copy-paste |

## Prod recommendation (Hamid)

1. Host service at `https://guide.sana-gps.ir` (Docker, port 4510).
2. Embed explicitly in each app layout via env `NEXT_PUBLIC_GUIDE_URL` / Blade partial.
3. Use nginx `sub_filter` inject **on stage only** for fast iteration.

## Integration checklist for new project

1. Add `data-guide="…"` on interactive elements.
2. Create `tours/{projectId}/getting-started.json`.
3. Add script tag or `GuideEmbed` component.
4. Bump tour `version` when UI flow changes.
