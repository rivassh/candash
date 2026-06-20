#!/usr/bin/env bash
# Fetch Cursor usage CSV without pasting cookies into chat.
# Cookie: ~/.config/cursor-usage.env  ?  CURSOR_USAGE_COOKIE='...'
#
# Usage:
#   bash fetch-cursor-usage-csv.sh 2026-06-14 2026-06-20 > ../usage/week.csv
set -euo pipefail

START_DATE="${1:?start YYYY-MM-DD}"
END_DATE="${2:?end YYYY-MM-DD}"
ENV_FILE="${CURSOR_USAGE_ENV:-${HOME}/.config/cursor-usage.env}"

if [[ -f "$ENV_FILE" ]]; then
  # shellcheck disable=SC1090
  source "$ENV_FILE"
fi

if [[ -z "${CURSOR_USAGE_COOKIE:-}" ]]; then
  echo "Set CURSOR_USAGE_COOKIE in $ENV_FILE (never commit)." >&2
  exit 1
fi

start_ms="$(date -d "${START_DATE} 00:00:00 UTC" +%s)000"
end_ms="$(date -d "${END_DATE} 23:59:59 UTC" +%s)999"

url="https://cursor.com/api/dashboard/export-usage-events-csv?startDate=${start_ms}&endDate=${end_ms}&strategy=tokens"

curl -fsSL "$url" \
  -H "Cookie: ${CURSOR_USAGE_COOKIE}" \
  -H 'Accept: text/csv,*/*'
