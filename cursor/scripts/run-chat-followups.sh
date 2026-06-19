#!/usr/bin/env bash
# Run pending follow-up tasks for all registered chats.
#
# Usage:
#   ./cursor/scripts/run-chat-followups.sh              # all enabled chats
#   ./cursor/scripts/run-chat-followups.sh 2026-06-19-stage-archive   # one chat
#   FORCE=1 ./cursor/scripts/run-chat-followups.sh      # rerun done steps too
#   SKIP_COMMIT=1 SKIP_LLFS_PUSH=1 ./cursor/scripts/run-chat-followups.sh
#   MINICRM_ACTION=keep ./cursor/scripts/run-chat-followups.sh
#
set -euo pipefail

LLFS_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
MANIFEST="${LLFS_ROOT}/cursor/follow-ups/manifest.tsv"
FOLLOWUPS_DIR="${LLFS_ROOT}/cursor/follow-ups"

export LLFS_ROOT

log()  { printf '[run-followups] %s\n' "$*"; }
warn() { printf '[run-followups][warn] %s\n' "$*" >&2; }

usage() {
  sed -n '2,10p' "$0" | sed 's/^# \?//'
  echo ""
  echo "Registered chats:"
  awk -F'\t' '!/^#/ && NF>=4 && $4==1 {printf "  %s — %s\n", $1, $2}' "$MANIFEST" 2>/dev/null || true
  echo ""
  echo "State: ${FOLLOWUPS_DIR}/.state/<chat_id>/<step>.done"
}

list_chats() {
  awk -F'\t' '
    /^#/ || NF < 4 { next }
    $4 == 1 { print $1 "\t" $3 }
  ' "$MANIFEST"
}

run_chat() {
  local chat_id="$1" script_name="$2"
  local script_path="${FOLLOWUPS_DIR}/${script_name}"
  if [[ ! -f "$script_path" ]]; then
    warn "missing script for ${chat_id}: ${script_path}"
    return 1
  fi
  log "chat: ${chat_id} (${script_name})"
  bash "$script_path"
}

main() {
  [[ -f "$MANIFEST" ]] || { warn "manifest not found: $MANIFEST"; exit 1; }

  if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    usage
    exit 0
  fi

  if [[ "${1:-}" == "--list" ]]; then
    list_chats
    exit 0
  fi

  local filter="${1:-}"
  local ran=0 failed=0

  while IFS=$'\t' read -r chat_id _title script_name _enabled; do
    [[ -n "$filter" && "$chat_id" != "$filter" ]] && continue
    if run_chat "$chat_id" "$script_name"; then
      ran=$((ran + 1))
    else
      failed=$((failed + 1))
    fi
  done < <(list_chats)

  if [[ "$ran" -eq 0 && -n "$filter" ]]; then
    warn "chat not found or disabled: $filter"
    exit 1
  fi

  log "done: ${ran} chat(s), ${failed} failed"
  [[ "$failed" -eq 0 ]]
}

main "$@"
