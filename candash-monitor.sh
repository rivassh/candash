#!/bin/bash
LOGFILE="/var/log/candash-collaborators.log"
PROJECT_DIR="/opt/websites/candash"
STATE_DIR="/tmp/candash-monitor-state"

mkdir -p "$STATE_DIR"

log_event() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOGFILE"
}

# --- Git activity: check for NEW commits ---
LAST_GIT_REV_FILE="$STATE_DIR/last_git_rev"
if [ ! -f "$LAST_GIT_REV_FILE" ]; then
    cd "$PROJECT_DIR" && git rev-parse HEAD 2>/dev/null > "$LAST_GIT_REV_FILE"
fi

current_git_rev=$(cd "$PROJECT_DIR" && git rev-parse HEAD 2>/dev/null || echo "")
last_git_rev=$(cat "$LAST_GIT_REV_FILE" 2>/dev/null || echo "")

if [ -n "$last_git_rev" ] && [ -n "$current_git_rev" ] && [ "$last_git_rev" != "$current_git_rev" ]; then
    commit_info=$(cd "$PROJECT_DIR" && git log --oneline -1 --format="%h %an: %s" 2>/dev/null)
    log_event "Git new commit: $commit_info"
    echo "$current_git_rev" > "$LAST_GIT_REV_FILE"
fi

# --- File modifications in last 30 seconds ---
find "$PROJECT_DIR" -type f -mmin -1 2>/dev/null > "$STATE_DIR/files_now"
LAST_FILES_CHECK="$STATE_DIR/last_files_check"
[ ! -f "$LAST_FILES_CHECK" ] && touch "$LAST_FILES_CHECK"
NEW_FILES=$(comm -13 "$LAST_FILES_CHECK" "$STATE_DIR/files_now" 2>/dev/null | head -10)
if [ -n "$NEW_FILES" ]; then
    files_list=$(echo "$NEW_FILES" | tr '\n' '; ')
    [ -n "$files_list" ] && log_event "Files modified: $files_list"
fi
mv "$STATE_DIR/files_now" "$LAST_FILES_CHECK"

# --- SSH sessions ---
SSHD_CURRENT="$STATE_DIR/sshd_current"
SSHD_PREVIOUS="$STATE_DIR/sshd_previous"
[ ! -f "$SSHD_PREVIOUS" ] && touch "$SSHD_PREVIOUS"
ps -ef | grep "[s]shd:" | grep -v "127.0.0.1" | awk '{print $2":"$6":"$7}' > "$SSHD_CURRENT"
NEW_SESSIONS=$(comm -13 "$SSHD_PREVIOUS" "$SSHD_CURRENT" 2>/dev/null)
if [ -n "$NEW_SESSIONS" ]; then
    while read session_id; do
        pid=$(echo "$session_id" | cut -d: -f1)
        session_info=$(ps -fp "$pid" 2>/dev/null | grep "[s]shd:" | head -1)
        [ -n "$session_info" ] && log_event "New SSH session: $session_info"
    done <<< "$NEW_SESSIONS"
fi
cp "$SSHD_CURRENT" "$SSHD_PREVIOUS"

# --- Project processes ---
PROJ_PROCS_CURRENT="$STATE_DIR/projs_current"
PROJ_PROCS_PREVIOUS="$STATE_DIR/projs_previous"
[ ! -f "$PROJ_PROCS_PREVIOUS" ] && touch "$PROJ_PROCS_PREVIOUS"
ps -ef | grep -E "(node|php|python|vite|artisan|candash)" | grep -v grep | \
    awk '{print $2":"substr($0, index($0,"/opt/websites/candash"))}' > "$PROJ_PROCS_CURRENT"
NEW_PROCS=$(comm -13 "$PROJ_PROCS_PREVIOUS" "$PROJ_PROCS_CURRENT" 2>/dev/null)
if [ -n "$NEW_PROCS" ]; then
    log_event "New project processes: $(echo "$NEW_PROCS" | head -3 | tr '\n' '; ')"
fi
cp "$PROJ_PROCS_CURRENT" "$PROJ_PROCS_PREVIOUS"

# --- MiMo/Memory activity ---
MIMO_MEMORY="/root/.local/share/mimocode/memory/sessions"
if [ -d "$MIMO_MEMORY" ]; then
    find "$MIMO_MEMORY" -name "*.md" -mmin -1 2>/dev/null > "$STATE_DIR/memory_now"
    LAST_MEMORY="$STATE_DIR/last_memory"
    [ ! -f "$LAST_MEMORY" ] && touch "$LAST_MEMORY"
    NEW_MEMORY=$(comm -13 "$LAST_MEMORY" "$STATE_DIR/memory_now" 2>/dev/null)
    if [ -n "$NEW_MEMORY" ]; then
        log_event "MiMo memory update: $NEW_MEMORY"
    fi
    mv "$STATE_DIR/memory_now" "$LAST_MEMORY"
fi

# --- Rate limit detection: only real API rate limit errors ---
# Check application-specific logs for actual rate limit errors
# Exclude kernel logs (which have false-positive "429" PIDs)
RATE_LIMIT_LOG="/var/log/candash-rate-limits.log"
RATE_LIMITS=""
for logfile in /var/log/syslog /var/log/messages /var/log/auth.log /opt/websites/candash/api/storage/logs/laravel.log; do
    if [ -f "$logfile" ]; then
        matches=$(grep -iE "HTTP 429|429 Too Many Requests|rate limit exceeded|rate_limit exceeded|too many requests|api.*rate.*limit" "$logfile" 2>/dev/null | tail -3)
        if [ -n "$matches" ]; then
            RATE_LIMITS="$RATE_LIMITS"$'\n'"$matches"
        fi
    fi
done
if [ -n "$RATE_LIMITS" ]; then
    echo "$RATE_LIMITS" | while read line; do
        [ -n "$line" ] && echo "[$(date '+%Y-%m-%d %H:%M:%S')] $line" >> "$RATE_LIMIT_LOG"
    done
fi

# --- MiMo process activity ---
MIMO_PROCS_CURRENT="$STATE_DIR/mimo_procs_current"
MIMO_PROCS_PREVIOUS="$STATE_DIR/mimo_procs_previous"
[ ! -f "$MIMO_PROCS_PREVIOUS" ] && touch "$MIMO_PROCS_PREVIOUS"
ps -ef | grep -E "mimo|mimocode|openrouter|anthropic|google" | grep -v grep | \
    awk '{print $2":"$8":"$9}' > "$MIMO_PROCS_CURRENT"
NEW_MIMO=$(comm -13 "$MIMO_PROCS_PREVIOUS" "$MIMO_PROCS_CURRENT" 2>/dev/null)
if [ -n "$NEW_MIMO" ]; then
    log_event "MiMo process activity: $NEW_MIMO"
fi
cp "$MIMO_PROCS_CURRENT" "$MIMO_PROCS_PREVIOUS"