#!/usr/bin/env bash
# Shared helpers for chat follow-up scripts.
set -euo pipefail

LLFS_ROOT="${LLFS_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
STATE_DIR="${LLFS_ROOT}/cursor/follow-ups/.state"
STAGE_DEPLOYER="${STAGE_DEPLOYER:-/opt/stage-deployer}"
STAGE_BASE="${STAGE_BASE:-https://stage.artandev.ir}"
NGINX_CONTAINER="${NGINX_CONTAINER:-nginx_proxy}"

mkdir -p "$STATE_DIR"

log()  { printf '[followup] %s\n' "$*"; }
warn() { printf '[followup][warn] %s\n' "$*" >&2; }
fail() { printf '[followup][fail] %s\n' "$*" >&2; exit 1; }

step_done() {
  local chat_id="$1" step="$2"
  [[ -f "${STATE_DIR}/${chat_id}/${step}.done" ]]
}

mark_step_done() {
  local chat_id="$1" step="$2"
  mkdir -p "${STATE_DIR}/${chat_id}"
  date -u +"%Y-%m-%dT%H:%M:%SZ" > "${STATE_DIR}/${chat_id}/${step}.done"
}

run_step() {
  local chat_id="$1" step="$2"
  shift 2
  if step_done "$chat_id" "$step" && [[ "${FORCE:-0}" != "1" ]]; then
    log "skip ${chat_id}/${step} (already done, FORCE=1 to rerun)"
    return 0
  fi
  log "run ${chat_id}/${step} ..."
  if "$@"; then
    mark_step_done "$chat_id" "$step"
    log "ok ${chat_id}/${step}"
  else
    warn "failed ${chat_id}/${step}"
    return 1
  fi
}

verify_http() {
  local url="$1" expect="${2:-200}"
  local code
  code="$(curl -fsSk --max-time "${CURL_TIMEOUT:-15}" -o /dev/null -w '%{http_code}' "$url" 2>/dev/null || echo "000")"
  if [[ "$expect" == *"|"* ]]; then
    local e
    IFS='|' read -ra opts <<< "$expect"
    for e in "${opts[@]}"; do
      [[ "$code" == "$e" ]] && { log "  $url → $code"; return 0; }
    done
    warn "  $url → $code (expected one of: $expect)"
    return 1
  fi
  if [[ "$code" == "$expect" ]]; then
    log "  $url → $code"
    return 0
  fi
  warn "  $url → $code (expected $expect)"
  return 1
}

nginx_reload() {
  docker exec "$NGINX_CONTAINER" nginx -s reload 2>/dev/null || warn "nginx reload failed"
}

git_commit_push() {
  local dir="$1" message="$2"
  [[ -d "$dir/.git" ]] || fail "not a git repo: $dir"
  cd "$dir"
  if git diff --quiet && git diff --cached --quiet && [[ -z "$(git status -s)" ]]; then
    log "  nothing to commit in $dir"
  else
    git add -A
    git -c user.email="${GIT_EMAIL:-devops@artandev.ir}" \
        -c user.name="${GIT_NAME:-stage-devops}" \
        commit -m "$message"
  fi
  local branch upstream
  branch="$(git rev-parse --abbrev-ref HEAD)"
  upstream="$(git rev-parse --abbrev-ref "${branch}@{upstream}" 2>/dev/null || true)"
  if [[ -n "$upstream" ]]; then
    git push origin "$branch"
  else
    git push -u origin "$branch"
  fi
}
