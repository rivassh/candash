# Open TODOs - debops project activity log closeout

<div dir="rtl" style="text-align: right;">

## Remaining Work

- [ ] `arvan-sanaradyab-panel-fix` - set `sanaradyab.ir` origin in Arvan panel to `94.182.193.115` with HTTP and a prepared port (`8080` or `8089`), then purge cache.
- [ ] `sanaradyab-origin-e2e` - after the Arvan fix, test `https://sanaradyab.ir/` externally, from Zabbix, and through the edge proxy.
- [ ] `ad-dc-172-16-1-246-recovery` - restore VM/network reachability for the Domain Controller at `172.16.1.246`; MikroTik and stage could not reach it.
- [ ] `nextcloud-ldap-watchdog-green` - after AD recovery, re-run `occ ldap:test-config s01` and the monitoring watchdog.
- [ ] `mikrotik-nat-hardening` - move critical RouterOS NAT rules from fixed `dst-address=94.182.193.115` toward a more resilient condition such as `in-interface=pppoe-out-Shatel-Radio`.
- [ ] `cursor-usage-exact-project-activity-log` - import a fresh Cursor Usage CSV and calculate exact cost for this closeout.

</div>
