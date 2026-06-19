#!/usr/bin/env bash
# Follow-ups for chat: stage project archive (2026-06-19)
set -euo pipefail

CHAT_ID="2026-06-19-stage-archive"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=/dev/null
source "${SCRIPT_DIR}/../scripts/lib/followup-common.sh"

KEEP_SLUGS="artexx,callcenter,chatbot-website,erp-guarantie,mix-proj,sanaradyab,modular-gps,nextcloud"
MINICRM_ACTION="${MINICRM_ACTION:-archive}"   # archive | keep
SKIP_COMMIT="${SKIP_COMMIT:-0}"
SKIP_LLFS_PUSH="${SKIP_LLFS_PUSH:-0}"

step_commit_stage_deployer() {
  [[ "$SKIP_COMMIT" == "1" ]] && { log "  SKIP_COMMIT=1"; return 0; }
  git_commit_push "$STAGE_DEPLOYER" "$(cat <<'EOF'
Add project archive system and bulk-archive stage projects.

Archive moves stacks to /opt/archived, updates YAML, UI filter/cookie for Mehdi group, and blocks deploy for archived projects.
EOF
)"
}

step_deploy_modular_gps() {
  cd "$STAGE_DEPLOYER"
  ./scripts/deploy-project.sh modular-gps main
}

step_handle_minicrm() {
  case "$MINICRM_ACTION" in
    archive)
      python3 "$STAGE_DEPLOYER/scripts/project_archive.py" archive minicrm-sms
      ;;
    keep)
      warn "minicrm-sms kept active (MINICRM_ACTION=keep)"
      ;;
    *)
      fail "MINICRM_ACTION must be archive or keep (got: $MINICRM_ACTION)"
      ;;
  esac
}

step_restart_stage_deployer() {
  cd "$STAGE_DEPLOYER"
  docker compose restart stage-deployer
  sleep 3
  nginx_reload
}

step_verify_keep_urls() {
  local ok=0
  verify_http "${STAGE_BASE}/stage/" "200" || ok=1
  verify_http "${STAGE_BASE}/artexx/" "308" || ok=1
  verify_http "${STAGE_BASE}/callcenter/" "200|308" || ok=1
  verify_http "${STAGE_BASE}/chatbot-website/" "200|308" || ok=1
  verify_http "${STAGE_BASE}/erp-guarantie/" "200|308" || ok=1
  verify_http "${STAGE_BASE}/mix-proj/" "200|308" || ok=1
  verify_http "${STAGE_BASE}/sanaradyab/" "200|308" || ok=1
  verify_http "${STAGE_BASE}/modular-gps/" "200|308" || ok=1
  verify_http "${STAGE_BASE}/nextcloud/" "302|200|308" || ok=1
  [[ "$ok" -eq 0 ]]
}

step_push_llfs() {
  [[ "$SKIP_LLFS_PUSH" == "1" ]] && { log "  SKIP_LLFS_PUSH=1"; return 0; }
  git_commit_push "$LLFS_ROOT" "Update cursor docs and follow-up state"
}

main() {
  log "=== chat follow-up: ${CHAT_ID} ==="
  local failed=0

  run_step "$CHAT_ID" commit-stage-deployer step_commit_stage_deployer || failed=1
  run_step "$CHAT_ID" deploy-modular-gps step_deploy_modular_gps || failed=1
  run_step "$CHAT_ID" minicrm-"$MINICRM_ACTION" step_handle_minicrm || failed=1
  run_step "$CHAT_ID" restart-stage-deployer step_restart_stage_deployer || failed=1
  run_step "$CHAT_ID" verify-keep-urls step_verify_keep_urls || failed=1
  run_step "$CHAT_ID" push-llfs step_push_llfs || failed=1

  if [[ "$failed" -eq 0 ]]; then
    log "=== ${CHAT_ID} complete ==="
  else
    warn "=== ${CHAT_ID} finished with errors (rerun safe; done steps skipped) ==="
    exit 1
  fi
}

main "$@"
