#!/usr/bin/env python3
"""Export llfs daily markdown reports to PDF, including linked Cursor usage cost."""

from __future__ import annotations

import argparse
import importlib.util
import re
import subprocess
import sys
import tempfile
from pathlib import Path


def load_render_module():
    path = Path(__file__).resolve().parent / "lib" / "pdf_report_render.py"
    spec = importlib.util.spec_from_file_location("pdf_report_render", path)
    if spec is None or spec.loader is None:
        raise RuntimeError(f"Cannot load render module: {path}")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


render = load_render_module()

DATE_PREFIX = re.compile(
    r"^\d{4}-\d{2}-\d{2}(?:[-_]\d{4}-\d{2}-\d{2}|-\d{2})?(?:[-_])?"
)
REF_PATTERNS = (
    r"`((?:\.\./)*(?:cursor/)?usage/[^`]+\.md)`",
    r"`(reports/[^`]+\.md)`",
    r"\(((?:\.\./)*(?:cursor/)?usage/[^)]+\.md)\)",
    r"\((reports/[^)]+\.md)\)",
    r"\(([^)/\s]+-cursor-cost\.md)\)",
    r"\(([^)/\s]+-cursor-usage-cost\.md)\)",
)
NOISE_TOKENS = frozenset(
    {
        "cost",
        "by",
        "prompt",
        "chat",
        "usage",
        "cursor",
        "daily",
        "git",
        "report",
        "debops",
        "stage",
        "deployer",
        "2026",
        "06",
        "07",
        "08",
        "09",
        "10",
        "11",
        "12",
        "13",
        "14",
        "15",
        "16",
        "17",
        "18",
        "19",
        "20",
        "21",
    }
)


def repo_root() -> Path:
    return Path(__file__).resolve().parents[2]


def daily_sources(root: Path) -> list[Path]:
    files: list[Path] = []
    files.extend(sorted(root.glob("reports/*daily*.md")))
    files.extend(sorted(root.glob("cursor/daily-reports/*.md")))
    seen: set[Path] = set()
    unique: list[Path] = []
    for path in files:
        resolved = path.resolve()
        if resolved not in seen:
            seen.add(resolved)
            unique.append(path)
    return unique


def cost_catalog(root: Path) -> list[Path]:
    files: list[Path] = []
    files.extend(sorted(root.glob("cursor/usage/*.md")))
    files.extend(sorted(root.glob("reports/*cost*.md")))
    files.extend(sorted(root.glob("reports/*cursor-usage*.md")))
    seen: set[Path] = set()
    unique: list[Path] = []
    for path in files:
        resolved = path.resolve()
        if resolved not in seen and "summary" not in path.name:
            seen.add(resolved)
            unique.append(path)
    return unique


def read_text(path: Path) -> str:
    raw = path.read_bytes()
    for encoding in ("utf-8", "utf-8-sig", "cp1252", "latin-1"):
        try:
            return raw.decode(encoding)
        except UnicodeDecodeError:
            continue
    return raw.decode("utf-8", errors="replace")


def is_cost_markdown(path: Path) -> bool:
    name = path.name.lower()
    if path.parent.name == "usage":
        return True
    return any(token in name for token in ("cost", "usage-cost", "cursor-cost"))


def resolve_reference(ref: str, root: Path, src: Path) -> Path | None:
    cleaned = ref.strip().replace("\\", "/")
    while cleaned.startswith("../"):
        cleaned = cleaned[3:]

    candidates: list[Path] = []
    if cleaned.startswith(("cursor/", "reports/")):
        candidates.append(root / cleaned)
    elif cleaned.startswith("usage/"):
        candidates.append(root / "cursor" / cleaned)
    elif src.parent.name == "reports":
        candidates.append(root / "reports" / cleaned)
        candidates.append(root / "cursor" / "usage" / Path(cleaned).name)
    else:
        candidates.append(root / "cursor" / "usage" / Path(cleaned).name)
        candidates.append(root / cleaned)

    for candidate in candidates:
        if candidate.is_file() and candidate.suffix == ".md" and is_cost_markdown(candidate):
            return candidate.resolve()
    return None


def extract_linked_cost_files(text: str, root: Path, src: Path) -> list[Path]:
    found: list[Path] = []
    seen: set[Path] = set()
    for pattern in REF_PATTERNS:
        for match in re.finditer(pattern, text):
            resolved = resolve_reference(match.group(1), root, src)
            if resolved and resolved not in seen:
                seen.add(resolved)
                found.append(resolved)
    return found


def topic_slug(path: Path) -> str:
    stem = path.stem
    for suffix in ("-daily-git-report", "-git-daily", "-daily"):
        if stem.endswith(suffix):
            stem = stem[: -len(suffix)]
            break
    stem = DATE_PREFIX.sub("", stem)
    return stem.strip("-_")


def token_set(text: str) -> set[str]:
    return {tok for tok in re.split(r"[-_]", text.lower()) if tok and tok not in NOISE_TOKENS}


def guess_cost_files(path: Path, root: Path, catalog: list[Path]) -> list[Path]:
    slug = topic_slug(path)
    if not slug:
        return []

    slug_tokens = token_set(slug)
    scored: list[tuple[int, Path]] = []
    for candidate in catalog:
        overlap = len(slug_tokens & token_set(candidate.stem))
        if overlap >= 2:
            scored.append((overlap, candidate))
        elif overlap == 1 and slug in candidate.stem:
            scored.append((overlap, candidate))

    if not scored:
        return []

    best = max(score for score, _ in scored)
    return [item for score, item in scored if score == best]


def find_cost_files(path: Path, text: str, root: Path, catalog: list[Path]) -> list[Path]:
    linked = extract_linked_cost_files(text, root, path)
    if linked:
        return linked

    guessed = guess_cost_files(path, root, catalog)
    if guessed:
        return guessed

    summary = root / "cursor" / "usage" / "summary-2026-06-14_2026-06-20.md"
    if path.parent.name == "daily-reports" and path.stem.isdigit() and summary.is_file():
        if "summary-" in text or re.search(r"Cursor\s*/\s*هزینه", text):
            return [summary.resolve()]
    return []


def build_html_document(path: Path, text: str, root: Path, catalog: list[Path]) -> str:
    daily_md, appendix_refs = render.prepare_daily_markdown(text)
    daily_html = render.md_to_html(daily_md)

    cost_files = find_cost_files(path, text, root, catalog)
    cost_blocks: list[tuple[str, str]] = []
    for cost_path in cost_files:
        rel = cost_path.relative_to(root).as_posix()
        label = render.link_label(rel)
        cost_md = render.prepare_cost_markdown(read_text(cost_path))
        cost_blocks.append((label, render.md_to_html(cost_md)))

    title = render.persian_title_from_path(path)
    source_path = path.relative_to(root).as_posix()
    return render.build_html_document(
        title=title,
        source_path=source_path,
        daily_html=daily_html,
        appendix_refs=appendix_refs,
        cost_blocks=cost_blocks,
        missing_cost=not cost_blocks,
    )


def pick_chrome() -> list[str]:
    for candidate in (
        "google-chrome",
        "chromium-browser",
        "chromium",
    ):
        try:
            subprocess.run(
                [candidate, "--version"],
                check=True,
                stdout=subprocess.DEVNULL,
                stderr=subprocess.DEVNULL,
            )
            return [candidate]
        except (OSError, subprocess.CalledProcessError):
            continue
    raise RuntimeError("No Chrome/Chromium binary found for PDF export")


def render_pdf(chrome: list[str], html_path: Path, pdf_path: Path) -> None:
    pdf_path.parent.mkdir(parents=True, exist_ok=True)
    cmd = [
        *chrome,
        "--headless=new",
        "--disable-gpu",
        "--no-sandbox",
        "--run-all-compositor-stages-before-draw",
        "--virtual-time-budget=15000",
        f"--print-to-pdf={pdf_path}",
        html_path.as_uri(),
    ]
    subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.PIPE)


def pdf_path_for_source(src: Path, out_dir: Path, root: Path) -> Path:
    rel = src.relative_to(root)
    if rel.parts[0] == "reports":
        pdf_rel = Path("reports") / rel.with_suffix(".pdf").name
    else:
        pdf_rel = Path("cursor/daily-reports") / rel.with_suffix(".pdf").name
    return out_dir / pdf_rel


def needs_export(
    src: Path,
    pdf_path: Path,
    *,
    missing_only: bool,
    include_stale: bool,
) -> bool:
    if not missing_only and not include_stale:
        return True
    if not pdf_path.is_file() or pdf_path.stat().st_size == 0:
        return True
    if include_stale and src.stat().st_mtime > pdf_path.stat().st_mtime:
        return True
    return False


def select_sources(
    sources: list[Path],
    out_dir: Path,
    root: Path,
    *,
    missing_only: bool,
    include_stale: bool,
) -> tuple[list[Path], list[Path]]:
    pending: list[Path] = []
    skipped: list[Path] = []
    for src in sources:
        pdf_path = pdf_path_for_source(src, out_dir, root)
        if needs_export(
            src,
            pdf_path,
            missing_only=missing_only,
            include_stale=include_stale,
        ):
            pending.append(src)
        else:
            skipped.append(src)
    return pending, skipped


def export_one(
    chrome: list[str],
    src: Path,
    out_dir: Path,
    root: Path,
    catalog: list[Path],
) -> Path:
    pdf_path = pdf_path_for_source(src, out_dir, root)
    text = read_text(src)
    document = build_html_document(src, text, root, catalog)

    with tempfile.NamedTemporaryFile("w", suffix=".html", delete=False, encoding="utf-8") as tmp:
        tmp.write(document)
        html_path = Path(tmp.name)

    try:
        render_pdf(chrome, html_path, pdf_path)
    finally:
        html_path.unlink(missing_ok=True)

    return pdf_path


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--out-dir",
        type=Path,
        default=repo_root() / "exports" / "pdf" / "daily-reports",
        help="Directory for generated PDF files",
    )
    mode = parser.add_mutually_exclusive_group()
    mode.add_argument(
        "--missing-only",
        action="store_true",
        help="Only export daily reports that do not yet have a PDF",
    )
    mode.add_argument(
        "--all",
        action="store_true",
        help="Export every daily report (default)",
    )
    parser.add_argument(
        "--stale",
        action="store_true",
        help="With --missing-only, also rebuild PDFs older than their markdown source",
    )
    parser.add_argument(
        "--refresh-render",
        action="store_true",
        help="Rebuild all PDFs (e.g. after template changes)",
    )
    args = parser.parse_args()

    root = repo_root()
    sources = daily_sources(root)
    if not sources:
        print("No daily report markdown files found.", file=sys.stderr)
        return 1

    missing_only = bool(args.missing_only) and not args.refresh_render

    pending, skipped = select_sources(
        sources,
        args.out_dir,
        root,
        missing_only=missing_only,
        include_stale=args.stale,
    )

    if not pending:
        print("No missing daily-report PDFs.", file=sys.stderr)
        if skipped:
            print(f"Skipped {len(skipped)} existing PDF(s).", file=sys.stderr)
        return 0

    catalog = cost_catalog(root)
    chrome = pick_chrome()
    generated: list[Path] = []
    for src in pending:
        pdf = export_one(chrome, src, args.out_dir, root, catalog)
        generated.append(pdf)
        print(pdf)

    print(
        f"\nGenerated {len(generated)} PDF(s) under {args.out_dir}",
        file=sys.stderr,
    )
    if skipped:
        print(f"Skipped {len(skipped)} existing PDF(s).", file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
