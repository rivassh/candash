# Learnings from the sana-gps chat

## Operational learnings

1. **`502` always needs layer isolation first**
   - Direct-origin `200` with public-domain `502` usually means path/CDN/origin mapping issue, not app health.

2. **Router NAT drift can look like CDN failure**
   - A single wrong `dstnat` target (`80/443` to the wrong internal host) can fully mimic external provider problems.

3. **Fixed public-IP NAT rules are brittle**
   - Binding critical rules to a hardcoded public radio IP increases outage risk when WAN assignment changes.

4. **Management baseline matters during incidents**
   - Keeping expected admin path (`sysadmin`, key auth, known ports) shortens MTTR significantly.

5. **Alert escalation should be staged**
   - SMS-first then delayed call reduces noise and keeps urgent outages actionable.

6. **Per-prompt cost attribution is approximate in current CSV**
   - Usage export is token-event based and lacks chat id in this dataset; exact per-chat billing requires additional correlation metadata.
