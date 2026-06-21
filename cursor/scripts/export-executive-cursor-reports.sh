#!/usr/bin/env bash
# Executive Cursor summaries for management (Persian, compact, cost-focused).
#
# Usage:
#   bash cursor/scripts/export-executive-cursor-reports.sh
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LLFS_DIR="${LLFS_DIR:-$(cd "${SCRIPT_DIR}/../.." && pwd)}"
VENV_DIR="${LLFS_DIR}/.venv-pdf"
PY="${VENV_DIR}/bin/python"

if [[ ! -x "$PY" ]]; then
  python3 -m venv "$VENV_DIR"
  "$PY" -m pip install -q --upgrade pip markdown
fi

exec "$PY" "${SCRIPT_DIR}/export-executive-cursor-reports.py" "$@"
