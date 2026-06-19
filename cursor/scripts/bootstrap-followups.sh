#!/usr/bin/env bash
# Clone or update llfs, then run chat follow-ups.
# One-liner:
#   curl -fsSL https://raw.githubusercontent.com/rivassh/llfs/main/cursor/scripts/bootstrap-followups.sh | bash
#
# Options (env):
#   LLFS_DIR=~/.local/share/llfs   install location
#   LLFS_REPO=git@github.com:rivassh/llfs.git
#   LLFS_BRANCH=main
#   ... plus run-chat-followups.sh args (e.g. FORCE=1, chat id)
#
set -euo pipefail

LLFS_DIR="${LLFS_DIR:-${HOME}/.local/share/llfs}"
LLFS_REPO="${LLFS_REPO:-https://github.com/rivassh/llfs.git}"
LLFS_BRANCH="${LLFS_BRANCH:-main}"

log() { printf '[llfs-bootstrap] %s\n' "$*"; }

if [[ -d "${LLFS_DIR}/.git" ]]; then
  log "update ${LLFS_DIR}"
  git -C "$LLFS_DIR" fetch origin "$LLFS_BRANCH" --quiet
  git -C "$LLFS_DIR" checkout "$LLFS_BRANCH" --quiet
  git -C "$LLFS_DIR" pull --ff-only origin "$LLFS_BRANCH" --quiet
else
  log "clone ${LLFS_REPO} → ${LLFS_DIR}"
  mkdir -p "$(dirname "$LLFS_DIR")"
  git clone --depth 1 --branch "$LLFS_BRANCH" "$LLFS_REPO" "$LLFS_DIR"
fi

exec bash "${LLFS_DIR}/cursor/scripts/run-chat-followups.sh" "$@"
