# Prompt summary � sana-gps outage to monitoring escalation

Conversation period: 2026-06-14 .. 2026-06-20

## Main prompt clusters

1. **Outage diagnosis (`502`)**
   - verify whether issue is server-side (`172.16.1.134`) or CDN/origin path
   - compare direct-origin responses vs public domain behavior

2. **MikroTik path and backup verification**
   - confirm historical NAT/routing behavior from backup exports
   - validate whether radio/fiber path assumptions match RouterOS state

3. **Live recovery on MikroTik**
   - obtain emergency credentials
   - inspect active NAT and service configuration
   - correct wrong `dstnat` target for `80/443`
   - verify `sana-gps.ir` and `app.sana-gps.ir` from multiple vantage points

4. **Config normalization request**
   - restore management settings to repository-expected baseline
   - ensure `sysadmin` access path remains operational

5. **Reliability automation**
   - implement monitoring watchdog:
     - immediate SMS on outage
     - voice call after 1 minute if still unresolved
   - deploy on monitoring host with scheduled execution

6. **Closeout/reporting requests**
   - build activity log from full conversation history
   - keep unfinished work in TODO
   - produce cost-oriented documentation from Cursor usage CSV export
   - prepare daily report and push artifacts to `git@github.com:rivassh/llfs.git`

## Key outcomes

- Public availability for `sana-gps.ir` and `app.sana-gps.ir` recovered during chat.
- Root operational gap identified: NAT brittleness around fixed public IP dependency.
- Alerting escalation path is now scripted and deployed, pending controlled E2E drill.
