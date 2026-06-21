# Prompt summary — debops activity log (transcript 1241c6d0)

## Timeline of prompts (merged themes)

### 2026-06-13 — outage / WAN / routing

- Internet outage after MikroTik reboot; 502 on hotspot; fiber vs radio split for staff.
- Charge 1000GB quota; Zabbix SMS alerts at 80/90/95%; verify fiber path; report why ~300GB consumed fast.
- Route VoIP + GPS `172.16.1.134` via radio; rest via fiber; route `git.artandev.ir` via radio.
- CEO explanation for GPS egress ~20–22 Mbps (~98% WAN); second management report + Nextcloud share.

### 2026-06-13 / 14 — VoIP / VPN / monitoring

- Extension 305 Service Unavailable; OpenVPN MikroTik ? Zoiper ? FreePBX `172.16.1.2`.
- Zabbix monitor ext305 registration; OpenVPN reachability probe fiber?radio.
- Printer `172.16.1.141` offline — Zabbix alert; stage down — unified health view.
- VoIP failover before MikroTik reset; Follow Me hunt to six mobiles; IVR key 3 support queue 9902 flow.

### 2026-06-14+ — support queue 9902 (major thread)

- Queue position TTS, waiting announcements, DND removal, ext?mobile mapping 302–305.
- Fix QAANNOUNCE comma bug, periodic-announce, failover order, survey/recording prompts.
- Failed-call TSV reports; multi-window analytics; ext 105/307; female TTS polish.
- On-call round-robin; disable specific failover numbers temporarily.

### Other infra in same chat

- `app.sana-gps.ir` OTP/SMS monitoring TODO.
- OpenVPN client configs in backup scope.
- WordPress sanaradyab bind mount + PDF invoice plugin visibility in wp-admin.
- Monitoring disk +100GB (incomplete — no LVM / disk not visible).

### 2026-06-21 — activity log + llfs close

- Build project activity log from **full conversation history** with strict columns and office-hours rule.
- Deliverable: `docs/ACTIVITY-LOG-2026-06-13-14.md` (18 merged rows; dates inferred without JSONL timestamps).
- Run `llfs close TOPIC` as `debops-activity-log-2026-06-13-14`.

## Technical decisions

- Activity log format matches `docs/ACTIVITY-LOG-2026-06-07-16.md` style but scoped to this chat window.
- When transcript lacks timestamps, infer dates from server logs, report headers, and file metadata — document uncertainty in closeout.
- Do not commit unrelated debops dirty tree during closeout; llfs submodule only.
