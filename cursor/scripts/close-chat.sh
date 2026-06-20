#!/usr/bin/env bash
# Mechanical llfs closeout — CSV, git daily, doc skeletons. Agent fills learnings only.
#
# Usage:
#   close-chat.sh TOPIC [--start YYYY-MM-DD] [--end YYYY-MM-DD]
#   close-chat.sh TOPIC --push          # commit + push llfs after agent edited learnings
#   close-chat.sh TOPIC --skip-csv
#
# Cursor (minimal tokens):
#   llfs close TOPIC
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=/dev/null
source "${SCRIPT_DIR}/lib/close-chat-common.sh"

TOPIC=""
START_DATE=""
END_DATE=""
SKIP_CSV=0
PUSH_ONLY=0
FORCE=0
WORKSPACE="${WORKSPACE:-/opt/stage-deployer}"
EXTRA_REPOS="${EXTRA_REPOS:-/opt/stage-deployer/devops}"

usage() {
  sed -n '2,12p' "$0" | sed 's/^# \?//'
  echo ""
  echo "Examples:"
  echo "  bash cursor/scripts/close-chat.sh stage-webhook"
  echo "  bash cursor/scripts/close-chat.sh debops --start 2026-06-14 --push"
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    -h|--help) usage; exit 0 ;;
    --start) START_DATE="$2"; shift 2 ;;
    --end) END_DATE="$2"; shift 2 ;;
    --workspace) WORKSPACE="$2"; shift 2 ;;
    --repos) EXTRA_REPOS="$2"; shift 2 ;;
    --skip-csv) SKIP_CSV=1; shift ;;
    --push) PUSH_ONLY=1; shift ;;
    --force) FORCE=1; shift ;;
    --)
      shift
      break
      ;;
    -*)
      warn "unknown option: $1"
      usage
      exit 1
      ;;
    *)
      if [[ -z "$TOPIC" ]]; then
        TOPIC="$1"
      else
        warn "unexpected arg: $1"
        exit 1
      fi
      shift
      ;;
  esac
done

[[ -n "$TOPIC" ]] || { usage; exit 1; }

TOPIC_SLUG="$(slugify "$TOPIC")"
START_DATE="${START_DATE:-$(default_start_date)}"
END_DATE="${END_DATE:-$TODAY_UTC}"

CLOSEOUT="${LLFS_ROOT}/chats/${TODAY_UTC}-${TOPIC_SLUG}-closeout.md"
DAILY="${LLFS_ROOT}/reports/${TODAY_UTC}-${TOPIC_SLUG}-git-daily.md"
COST="${LLFS_ROOT}/cursor/usage/${TOPIC_SLUG}-${START_DATE}_${END_DATE}-cost.md"
TODO_OPEN="${LLFS_ROOT}/cursor/todos/${TODAY_UTC}-${TOPIC_SLUG}-chat-open.md"
PROMPTS="${LLFS_ROOT}/cursor/chats/${TODAY_UTC}-${TOPIC_SLUG}-prompts.md"
CSV_OUT="${LLFS_ROOT}/cursor/usage/${START_DATE}_${END_DATE}.csv"

if [[ "$PUSH_ONLY" == "1" ]]; then
  llfs_git_push "llfs close: ${TOPIC_SLUG} (${TODAY_UTC})"
  log "pushed llfs"
  exit 0
fi

mkdir -p "${LLFS_ROOT}/chats" "${LLFS_ROOT}/reports" "${LLFS_ROOT}/cursor/usage" \
         "${LLFS_ROOT}/cursor/todos" "${LLFS_ROOT}/cursor/chats"

# --- CSV ---
CSV_STATUS="skipped"
if [[ "$SKIP_CSV" == "1" ]]; then
  CSV_STATUS="skipped (--skip-csv)"
elif [[ -x "${SCRIPTS_DIR}/fetch-cursor-usage-csv.sh" ]]; then
  if "${SCRIPTS_DIR}/fetch-cursor-usage-csv.sh" "$START_DATE" "$END_DATE" > "$CSV_OUT" 2>/dev/null; then
    CSV_STATUS="ok → ${CSV_OUT#${LLFS_ROOT}/}"
  else
    CSV_STATUS="failed — set CURSOR_USAGE_COOKIE in ~/.config/cursor-usage.env (never commit)"
    rm -f "$CSV_OUT"
  fi
else
  CSV_STATUS="fetch script missing"
fi

# --- Git daily report ---
if [[ "$FORCE" == "1" || ! -f "$DAILY" ]]; then
  {
    echo "# گزارش Git — ${TOPIC_SLUG} — ${TODAY_UTC}"
    echo ""
    echo "**Topic:** ${TOPIC}"
    echo "**Workspace:** \`${WORKSPACE}\`"
    echo "**Generated:** ${NOW_UTC}"
    echo ""
    git_section "$WORKSPACE" "$(basename "$WORKSPACE")" "$START_DATE"
    IFS=',' read -ra extra <<< "$EXTRA_REPOS"
    for repo in "${extra[@]}"; do
      repo="$(echo "$repo" | xargs)"
      [[ -n "$repo" ]] && git_section "$repo" "$(basename "$repo")" "$START_DATE"
    done
    echo "## llfs"
    echo ""
    echo "- Closeout: [\`chats/${TODAY_UTC}-${TOPIC_SLUG}-closeout.md\`](../chats/${TODAY_UTC}-${TOPIC_SLUG}-closeout.md)"
    echo "- Cost: [\`cursor/usage/${TOPIC_SLUG}-${START_DATE}_${END_DATE}-cost.md\`](../cursor/usage/${TOPIC_SLUG}-${START_DATE}_${END_DATE}-cost.md)"
    echo ""
  } > "$DAILY"
  log "wrote ${DAILY#${LLFS_ROOT}/}"
else
  log "exists ${DAILY#${LLFS_ROOT}/} (use --force to overwrite)"
fi

# --- Cost stub ---
if [[ "$FORCE" == "1" || ! -f "$COST" ]]; then
  {
    echo "# Cursor usage — ${TOPIC_SLUG} — ${START_DATE} … ${END_DATE}"
    echo ""
    echo "**Generated:** ${NOW_UTC}"
    echo "**CSV:** ${CSV_STATUS}"
    echo "**Security:** no cookies in this repo."
    echo ""
    echo "## Refresh CSV"
    echo ""
    echo '```bash'
    echo "bash ${SCRIPTS_DIR}/fetch-cursor-usage-csv.sh ${START_DATE} ${END_DATE} \\"
    echo "  > cursor/usage/${START_DATE}_${END_DATE}.csv"
    echo '```'
    echo ""
    echo "## Summary (AGENT — optional, after reading CSV)"
    echo ""
    echo "_Agent: add token/event summary if user asked; otherwise leave as pointer to CSV._"
    echo ""
  } > "$COST"
  log "wrote ${COST#${LLFS_ROOT}/}"
fi

# --- TODO open items stub ---
if [[ "$FORCE" == "1" || ! -f "$TODO_OPEN" ]]; then
  {
    echo "# Open TODO — ${TOPIC_SLUG} — ${TODAY_UTC}"
    echo ""
    echo "From chat closeout. Merged into [\`cursor/TODO.md\`](../TODO.md)."
    echo ""
    echo "## AGENT — unfinished from this chat"
    echo ""
    echo "- [ ] _Agent: list items; then merge into cursor/TODO.md_"
    echo ""
  } > "$TODO_OPEN"
  log "wrote ${TODO_OPEN#${LLFS_ROOT}/}"
fi

# --- Prompts stub (optional archive) ---
if [[ "$FORCE" == "1" || ! -f "$PROMPTS" ]]; then
  {
    echo "# Prompts — ${TOPIC_SLUG} — ${TODAY_UTC}"
    echo ""
    echo "**Do not paste Cursor cookies here.**"
    echo ""
    echo "## AGENT — short prompt summary (optional)"
    echo ""
    echo "_One line per user turn; no full paste of long closeout prompt._"
    echo ""
  } > "$PROMPTS"
fi

# --- Closeout (agent fills learnings) ---
if [[ "$FORCE" == "1" || ! -f "$CLOSEOUT" ]]; then
  {
    echo "# Chat closeout — ${TOPIC} — ${TODAY_UTC}"
    echo ""
    echo "**Workspace:** \`${WORKSPACE}\`"
    echo "**Generated:** ${NOW_UTC}"
    echo ""
    echo "## Learnings (AGENT — max 5 bullets)"
    echo ""
    echo "_Agent: replace this block after \`close-chat.sh\` — what the user did not know before this chat._"
    echo ""
    echo "## Prompt summary (AGENT — optional)"
    echo ""
    echo "| # | Theme | Result |"
    echo "|---:|---|---|"
    echo "| 1 | _Agent_ | _brief_ |"
    echo ""
    echo "## Artifacts"
    echo ""
    echo "| Type | Path |"
    echo "|------|------|"
    echo "| Git daily | [\`reports/${TODAY_UTC}-${TOPIC_SLUG}-git-daily.md\`](../reports/${TODAY_UTC}-${TOPIC_SLUG}-git-daily.md) |"
    echo "| Cost | [\`cursor/usage/${TOPIC_SLUG}-${START_DATE}_${END_DATE}-cost.md\`](../cursor/usage/${TOPIC_SLUG}-${START_DATE}_${END_DATE}-cost.md) |"
    echo "| TODO | [\`cursor/todos/${TODAY_UTC}-${TOPIC_SLUG}-chat-open.md\`](../cursor/todos/${TODAY_UTC}-${TOPIC_SLUG}-chat-open.md) |"
    echo "| Prompts | [\`cursor/chats/${TODAY_UTC}-${TOPIC_SLUG}-prompts.md\`](../cursor/chats/${TODAY_UTC}-${TOPIC_SLUG}-prompts.md) |"
    echo ""
    echo "## Security"
    echo ""
    echo "- Never commit Cursor session cookies."
    echo "- CSV: \`${CSV_STATUS}\`"
    echo ""
  } > "$CLOSEOUT"
  log "wrote ${CLOSEOUT#${LLFS_ROOT}/}"
fi

# --- Append pointer to master TODO ---
TODO_MASTER="${LLFS_ROOT}/cursor/TODO.md"
if ! grep -q "## ${TOPIC_SLUG} (from " "$TODO_MASTER" 2>/dev/null; then
  {
    echo ""
    echo "## ${TOPIC_SLUG} (from ${TODAY_UTC} chat)"
    echo ""
    echo "- [ ] **${TOPIC}** — [detail](todos/${TODAY_UTC}-${TOPIC_SLUG}-chat-open.md)"
    echo ""
    echo "**Last updated:** ${TODAY_UTC} (${TOPIC_SLUG} closeout)"
  } >> "$TODO_MASTER"
  log "appended section to cursor/TODO.md"
fi

log "done — agent: edit Learnings in ${CLOSEOUT#${LLFS_ROOT}/}"
log "then: bash cursor/scripts/close-chat.sh ${TOPIC_SLUG} --push"
printf '%s\n' "$CLOSEOUT" "$DAILY" "$COST" "$TODO_OPEN"
