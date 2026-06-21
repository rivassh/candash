"""Executive Persian summaries focused on Cursor usage (for management reports)."""

from __future__ import annotations

import importlib.util
import re
from pathlib import Path


def load_calc():
    path = Path(__file__).resolve().parent / "cursor_cost_calc.py"
    spec = importlib.util.spec_from_file_location("cursor_cost_calc", path)
    if spec is None or spec.loader is None:
        raise RuntimeError(path)
    module = importlib.util.module_from_spec(spec)
    import sys

    sys.modules["cursor_cost_calc"] = module
    spec.loader.exec_module(module)
    return module


calc = load_calc()

TOPIC_FA = {
    "debops-health-dashboard": "داشبورد سلامت monitoring",
    "debops-activity-log": "گزارش فعالیت debops",
    "debops-project-activity-log": "لاگ فعالیت پروژه",
    "debops-gitlab-zabbix": "GitLab / Zabbix / چاپگر",
    "debops-infra-audit": "ممیزی infra",
    "debops-metabase-nextcloud": "Metabase / Nextcloud",
    "nexus-openvpn": "Nexus / OpenVPN",
    "gps-activity-closeout": "GPS / activity",
    "stage-deployer-production-ui": "UI استیج",
    "stage-health-deploy": "استقرار health استیج",
    "passbolt-chat": "Passbolt",
    "network-gitlab-stage": "GitLab / شبکه استیج",
    "debops-sana-gps-nat": "NAT سana-gps",
    "2026-06-20-sana-gps-chat": "چت sana-gps",
}


def topic_key(path: Path) -> str:
    name = path.stem
    name = re.sub(r"-\d{4}-\d{2}-\d{2}.*$", "", name)
    name = re.sub(r"-cost.*$", "", name)
    return name


def topic_fa(path: Path) -> str:
    key = topic_key(path)
    return TOPIC_FA.get(key, key.replace("-", " "))


def parse_cost_table(text: str) -> list[dict[str, str]]:
    rows: list[dict[str, str]] = []
    lines = [ln.strip() for ln in text.splitlines() if ln.strip().startswith("|")]
    if len(lines) < 2:
        return rows
    headers = [h.strip() for h in lines[0].strip("|").split("|")]
    for line in lines[2:]:
        cells = [c.strip() for c in line.strip("|").split("|")]
        if len(cells) < len(headers):
            continue
        rows.append(dict(zip(headers, cells)))
    return rows


def parse_token_value(raw: str) -> int:
    text = (raw or "0").strip().replace(",", "")
    if not text or text in {"—", "-", "n/a"}:
        return 0
    if text.startswith("~"):
        text = text[1:]
    multipliers = {"k": 1_000, "m": 1_000_000, "b": 1_000_000_000}
    lower = text.lower()
    for suffix, mult in multipliers.items():
        if lower.endswith(suffix):
            try:
                return int(float(lower[:-1]) * mult)
            except ValueError:
                return 0
    try:
        return int(float(text))
    except ValueError:
        return 0


def parse_totals(text: str) -> tuple[int, int]:
    tokens = 0
    events = 0
    m = re.search(r"جمع توکن \(برآورد\):\*\*\s*([\d,~KMkm.]+)", text)
    if m:
        tokens = parse_token_value(m.group(1))
    m = re.search(r"جمع رویداد:\*\*\s*([\d,~KMkm.]+)", text)
    if m:
        events = parse_token_value(m.group(1))
    if tokens:
        return tokens, events

    rows = parse_cost_table(text)
    for row in rows:
        tok = row.get("توکن") or row.get("Total tokens") or "0"
        ev = row.get("رویداد") or row.get("Events") or "0"
        tokens += parse_token_value(str(tok))
        events += parse_token_value(str(ev))
    return tokens, events


def date_range(rows: list[dict[str, str]]) -> str:
    dates = []
    for row in rows:
        d = row.get("تاریخ") or row.get("Date") or ""
        if re.match(r"\d{4}-\d{2}-\d{2}", d):
            dates.append(d[:10])
    if not dates:
        return "—"
    return f"{min(dates)} → {max(dates)}"


def top_topics(rows: list[dict[str, str]], limit: int = 3) -> list[str]:
    scored: list[tuple[int, str]] = []
    for row in rows:
        topic = row.get("موضوع") or row.get("Prompt topic") or ""
        tok = row.get("توکن") or row.get("Total tokens") or "0"
        val = parse_token_value(str(tok))
        if topic:
            scored.append((val, topic[:90]))
    scored.sort(reverse=True)
    return [t for _, t in scored[:limit]]


def build_executive_markdown(cost_path: Path, root: Path) -> str:
    text = cost_path.read_text(encoding="utf-8", errors="replace")
    rows = parse_cost_table(text)
    tokens, events = parse_totals(text)
    title = topic_fa(cost_path)
    dr = date_range(rows)
    topics = top_topics(rows)

    lines = [
        f"# گزارش Cursor — {title}",
        "",
        '<div dir="rtl" style="text-align: right;">',
        "",
        "## خلاصه مدیریتی",
        "",
        "| شاخص | مقدار |",
        "|---|---|",
        f"| موضوع | {title} |",
        f"| بازه | {dr} |",
        f"| تعداد پرامپت/ردیف | {len(rows)} |",
        f"| توکن (برآورد) | {tokens:,} |",
        f"| رویداد CSV | {events:,} |",
        "| هزینه نقدی | **$0.00** (Included/Free) |",
        "",
        "## مهم‌ترین کارها (Cursor)",
        "",
    ]

    if topics:
        for item in topics:
            lines.append(f"- {item}")
    else:
        lines.append("- جزئیات در فایل cost کامل موجود است.")

    lines.extend(
        [
            "",
            "## جزئیات مصرف",
            "",
            "| تاریخ | موضوع | توکن |",
            "|---|---|---:|",
        ]
    )

    for row in rows[:8]:
        day = row.get("تاریخ") or row.get("Date") or "—"
        topic = (row.get("موضوع") or row.get("Prompt topic") or "—")[:70]
        tok = row.get("توکن") or row.get("Total tokens") or "0"
        lines.append(f"| {day} | {topic} | {tok} |")

    if len(rows) > 8:
        lines.append("")
        lines.append(f"> {len(rows) - 8} ردیف دیگر در `{cost_path.relative_to(root).as_posix()}`")

    lines.extend(
        [
            "",
            "## پیوست",
            "",
            f"- cost کامل: `{cost_path.relative_to(root).as_posix()}`",
            "",
            "</div>",
            "",
        ]
    )
    return "\n".join(lines)


def build_master_summary(cost_files: list[Path], root: Path) -> str:
    entries: list[tuple[int, str, str, int]] = []
    grand_tokens = 0
    grand_events = 0

    for path in cost_files:
        text = path.read_text(encoding="utf-8", errors="replace")
        rows = parse_cost_table(text)
        tokens, events = parse_totals(text)
        if tokens == 0 and not rows:
            continue
        grand_tokens += tokens
        grand_events += events
        entries.append((tokens, topic_fa(path), date_range(rows), len(rows)))

    entries.sort(reverse=True)

    lines = [
        "# خلاصه Cursor — همه چت‌های llfs",
        "",
        '<div dir="rtl" style="text-align: right;">',
        "",
        "## جمع کل (برآورد)",
        "",
        "| شاخص | مقدار |",
        "|---|---|",
        f"| تعداد گزارش | {len(entries)} |",
        f"| توکن | {grand_tokens:,} |",
        f"| رویداد | {grand_events:,} |",
        "| هزینه نقدی | **$0.00** (Included/Free) |",
        "",
        "> **توجه:** جمع، مجموع برآورد هر چت است و ممکن است با مصرف واقعی اکانت overlap داشته باشد.",
        "",
        "## رتبه‌بندی موضوعات",
        "",
        "| موضوع | بازه | پرامپت | توکن |",
        "|---|---|---:|---:|",
    ]

    for tokens, title, dr, count in entries:
        lines.append(f"| {title} | {dr} | {count} | {tokens:,} |")

    lines.extend(["", "</div>", ""])
    return "\n".join(lines)


def write_executive_reports(root: Path, out_md: Path) -> list[Path]:
    out_md.mkdir(parents=True, exist_ok=True)
    written: list[Path] = []
    cost_files = calc.iter_cost_files(root)

    for cost_path in cost_files:
        text = cost_path.read_text(encoding="utf-8", errors="replace")
        if "not_measured" in text.lower() and "توکن" not in text:
            continue
        md = build_executive_markdown(cost_path, root)
        out_path = out_md / f"{topic_key(cost_path)}-executive.md"
        out_path.write_text(md, encoding="utf-8")
        written.append(out_path)

    master = out_md / "ALL-cursor-executive-summary.md"
    master.write_text(build_master_summary(cost_files, root), encoding="utf-8")
    written.append(master)
    return written
