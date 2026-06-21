#!/usr/bin/env bash
# Build PDFs only for daily markdown reports that do not yet have a PDF export.
# Output includes linked Cursor usage cost sections (same format as full export).
#
# Usage:
#   bash cursor/scripts/export-missing-daily-reports-pdf.sh
#   bash cursor/scripts/export-missing-daily-reports-pdf.sh --stale
#   LLFS_DIR=/opt/new/debops/llfs bash cursor/scripts/export-missing-daily-reports-pdf.sh
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LLFS_DIR="${LLFS_DIR:-$(cd "${SCRIPT_DIR}/../.." && pwd)}"
VENV_DIR="${LLFS_DIR}/.venv-pdf"
PY="${VENV_DIR}/bin/python"
EXPORT_PY="${SCRIPT_DIR}/export-daily-reports-pdf.py"

if [[ ! -f "$EXPORT_PY" ]]; then
  echo "Missing exporter: $EXPORT_PY" >&2
  exit 1
fi

if [[ ! -x "$PY" ]]; then
  echo "Creating PDF export venv at $VENV_DIR" >&2
  python3 -m venv "$VENV_DIR"
  "$PY" -m pip install -q --upgrade pip
  "$PY" -m pip install -q markdown
fi

if ! "$PY" -c "import markdown" >/dev/null 2>&1; then
  "$PY" -m pip install -q markdown
fi

if ! command -v google-chrome >/dev/null 2>&1 \
  && ! command -v chromium-browser >/dev/null 2>&1 \
  && ! command -v chromium >/dev/null 2>&1; then
  echo "Need google-chrome, chromium-browser, or chromium for PDF export." >&2
  exit 1
fi

args=(--missing-only)
if [[ "${1:-}" == "--stale" ]]; then
  args+=(--stale)
elif [[ "${1:-}" == "--refresh" || "${1:-}" == "--all" ]]; then
  args=(--refresh-render)
elif [[ -n "${1:-}" ]]; then
  echo "Usage: $0 [--stale|--refresh]" >&2
  exit 1
fi

exec "$PY" "$EXPORT_PY" "${args[@]}"
