#!/usr/bin/env python3
"""Build executive Cursor summaries (markdown + PDF) for management reporting."""

from __future__ import annotations

import argparse
import importlib.util
import subprocess
import sys
import tempfile
from pathlib import Path


def load_module(name: str, path: Path):
    spec = importlib.util.spec_from_file_location(name, path)
    if spec is None or spec.loader is None:
        raise RuntimeError(path)
    module = importlib.util.module_from_spec(spec)
    sys.modules[name] = module
    spec.loader.exec_module(module)
    return module


def repo_root() -> Path:
    return Path(__file__).resolve().parents[2]


def pick_chrome() -> list[str]:
    for candidate in ("google-chrome", "chromium-browser", "chromium"):
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
    raise RuntimeError("Need Chrome/Chromium for PDF export")


def render_pdf(chrome: list[str], html_path: Path, pdf_path: Path) -> None:
    pdf_path.parent.mkdir(parents=True, exist_ok=True)
    subprocess.run(
        [
            *chrome,
            "--headless=new",
            "--disable-gpu",
            "--no-sandbox",
            "--run-all-compositor-stages-before-draw",
            "--virtual-time-budget=15000",
            f"--print-to-pdf={pdf_path}",
            html_path.as_uri(),
        ],
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.PIPE,
    )


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--skip-calc",
        action="store_true",
        help="Do not run calculate-cursor-costs first",
    )
    args = parser.parse_args()

    root = repo_root()
    lib = Path(__file__).resolve().parent / "lib"
    calc_script = Path(__file__).resolve().parent / "calculate-cursor-costs.py"
    executive = load_module("executive_report", lib / "executive_report.py")
    render = load_module("pdf_report_render", lib / "pdf_report_render.py")

    if not args.skip_calc:
        subprocess.run([sys.executable, str(calc_script)], check=False)

    md_dir = root / "exports" / "executive" / "markdown"
    pdf_dir = root / "exports" / "pdf" / "executive"
    written = executive.write_executive_reports(root, md_dir)

    chrome = pick_chrome()
    pdfs: list[Path] = []
    for md_path in written:
        text = md_path.read_text(encoding="utf-8")
        title_match = text.splitlines()[0].lstrip("# ").strip() if text else md_path.stem
        document = render.build_executive_html(
            title=title_match,
            source_path=md_path.relative_to(root).as_posix(),
            markdown_text=text,
        )
        pdf_path = pdf_dir / md_path.with_suffix(".pdf").name
        with tempfile.NamedTemporaryFile("w", suffix=".html", delete=False, encoding="utf-8") as tmp:
            tmp.write(document)
            html_path = Path(tmp.name)
        try:
            render_pdf(chrome, html_path, pdf_path)
        finally:
            html_path.unlink(missing_ok=True)
        pdfs.append(pdf_path)
        print(pdf_path)

    print(f"\nMarkdown: {md_dir}", file=sys.stderr)
    print(f"PDF: {pdf_dir} ({len(pdfs)} files)", file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
