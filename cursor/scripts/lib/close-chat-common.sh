#!/usr/bin/env bash
# Shared helpers for llfs close-chat.sh
set -euo pipefail

LLFS_ROOT="${LLFS_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)}"
SCRIPTS_DIR="${LLFS_ROOT}/cursor/scripts"
TODAY_UTC="$(date -u +%Y-%m-%d)"
NOW_UTC="$(date -u +%Y-%m-%dT%H:%M:%SZ)"

log()  { printf '[llfs-close] %s\n' "$*"; }
warn() { printf '[llfs-close][warn] %s\n' "$*" >&2; }

slugify() {
  echo "$1" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+|-+$//g'
}

default_start_date() {
  date -u -d "${TODAY_UTC} -7 days" +%Y-%m-%d 2>/dev/null \
    || date -u -v-7d +%Y-%m-%d
}

git_section() {
  local repo="$1" label="$2" since="$3"
  [[ -d "$repo/.git" ]] || return 0
  {
    echo "### Git — ${label}"
    echo ""
    echo "**Path:** \`${repo}\`"
    echo ""
    local branch upstream
    branch="$(git -C "$repo" rev-parse --abbrev-ref HEAD 2>/dev/null || echo "?")"
    upstream="$(git -C "$repo" rev-parse --abbrev-ref "${branch}@{upstream}" 2>/dev/null || true)"
    echo "**Branch:** \`${branch}\`${upstream:+ → \`$upstream\`}"
    echo ""
    echo "#### Commits (${since} … ${TODAY_UTC})"
    echo ""
    if git -C "$repo" log --since="${since}T00:00:00Z" --until="${TODAY_UTC}T23:59:59Z" \
        --oneline --no-decorate -n 40 2>/dev/null | grep -q .; then
      git -C "$repo" log --since="${since}T00:00:00Z" --until="${TODAY_UTC}T23:59:59Z" \
        --oneline --no-decorate -n 40 2>/dev/null | sed 's/^/- /'
    else
      echo "_No commits in range._"
    fi
    echo ""
    echo "#### Working tree"
    echo ""
    echo '```'
    git -C "$repo" status -sb 2>/dev/null | head -20 || true
    echo '```'
    echo ""
  }
}

llfs_git_push() {
  local message="$1"
  cd "$LLFS_ROOT"
  if git diff --quiet && git diff --cached --quiet && [[ -z "$(git status -s)" ]]; then
    log "nothing to commit in llfs"
    return 0
  fi
  git add -A
  git -c user.email="${GIT_EMAIL:-devops@artandev.ir}" \
      -c user.name="${GIT_NAME:-llfs-close}" \
      commit -m "$message"
  local branch
  branch="$(git rev-parse --abbrev-ref HEAD)"
  git fetch origin "$branch" 2>/dev/null || true
  if git rev-parse "origin/${branch}" >/dev/null 2>&1; then
    GIT_AUTHOR_EMAIL="${GIT_EMAIL:-devops@artandev.ir}" \
    GIT_AUTHOR_NAME="${GIT_NAME:-llfs-close}" \
    GIT_COMMITTER_EMAIL="${GIT_EMAIL:-devops@artandev.ir}" \
    GIT_COMMITTER_NAME="${GIT_NAME:-llfs-close}" \
      git rebase "origin/${branch}" || return 1
  fi
  if git rev-parse --abbrev-ref "${branch}@{upstream}" >/dev/null 2>&1; then
    git push origin "$branch"
  else
    git push -u origin "$branch"
  fi
}
