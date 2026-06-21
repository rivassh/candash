#!/usr/bin/env python3
"""Fill in Cursor cost markdown files from CSV + prompt timelines."""

from __future__ import annotations

import argparse
import importlib.util
import sys
from pathlib import Path


def load_module(name: str, path: Path):
    spec = importlib.util.spec_from_file_location(name, path)
    if spec is None or spec.loader is None:
        raise RuntimeError(f"Cannot load {path}")
    module = importlib.util.module_from_spec(spec)
    sys.modules[name] = module
    spec.loader.exec_module(module)
    return module


def repo_root() -> Path:
    return Path(__file__).resolve().parents[2]


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--file",
        type=Path,
        help="Update one cost markdown file only",
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="Recalculate even if a prior estimate exists",
    )
    args = parser.parse_args()

    root = repo_root()
    calc = load_module("cursor_cost_calc", Path(__file__).resolve().parent / "lib" / "cursor_cost_calc.py")

    if args.file:
        path = args.file if args.file.is_absolute() else root / args.file
        if not path.is_file():
            print(f"Not found: {path}", file=sys.stderr)
            return 1
        ok = calc.calculate_cost_file(path, root, force=args.force)
        print("updated" if ok else "skipped", path)
        return 0 if ok else 2

    updated = calc.calculate_all(root, force=args.force)
    if not updated:
        print("No cost files updated.", file=sys.stderr)
        return 0

    for path in updated:
        print(path.relative_to(root))
    print(f"\nUpdated {len(updated)} file(s).", file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
